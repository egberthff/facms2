import jsQR from 'jsqr';

/**
 * Decode a QR Ph (EMVCo) payment QR image, e.g. a GCash or InstaPay bank QR,
 * and pull out the payout details the claim form needs.
 *
 * Resolves to { provider, bank, number, name, nameMasked, raw }.
 * Rejects with a user-facing Error when the image has no readable QR.
 */

// Acquirer BIC prefix => provider label used by the claim form
const PROVIDERS = {
    GXCH: 'GCash',
    BNOR: 'BDO',
    BOPI: 'BPI',
    TLBP: 'LandBank',
    MBTC: 'Metrobank',
    PNBM: 'PNB',
};

const MOBILE_PATTERN = /^(?:\+?63|0)(9\d{9})$/;

export async function readPaymentQr(file) {
    const raw = await decodeImage(file);

    if (!raw) {
        throw new Error('No QR code was found in the image. Try a clearer, uncropped screenshot.');
    }

    return parsePayload(raw);
}

/* =====================================================
 * IMAGE DECODING
 * ===================================================== */

async function decodeImage(file) {
    const image = await loadImage(file);

    // Large photos decode faster and more reliably when downscaled; fall back to full size
    for (const maxSide of [1200, Math.max(image.width, image.height)]) {
        const scale = Math.min(1, maxSide / Math.max(image.width, image.height));
        const width = Math.round(image.width * scale);
        const height = Math.round(image.height * scale);

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(image, 0, 0, width, height);

        const result = jsQR(ctx.getImageData(0, 0, width, height).data, width, height);
        if (result?.data) {
            return result.data;
        }
    }

    return null;
}

function loadImage(file) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const image = new Image();

        image.onload = () => {
            URL.revokeObjectURL(url);
            resolve(image);
        };
        image.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('The selected file could not be read as an image.'));
        };

        image.src = url;
    });
}

/* =====================================================
 * PAYLOAD PARSING
 * ===================================================== */

function parsePayload(raw) {
    const details = { provider: null, bank: null, number: null, numberMasked: false, name: null, nameMasked: false, raw };

    // Not an EMVCo payload: best effort, look for a mobile number anywhere
    if (!raw.startsWith('000201')) {
        details.number = findMobileNumber([raw]);
        return details;
    }

    const tags = parseTlv(raw);

    // Merchant account information templates live in tags 26-51
    const accountValues = [];
    for (let id = 26; id <= 51; id++) {
        const template = tags[String(id)];
        if (!template) continue;

        const sub = parseTlv(template);
        const bic = Object.values(sub).find(value => /^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/.test(value));

        if (bic && !details.provider) {
            details.provider = PROVIDERS[bic.slice(0, 4)] || bic;
        }

        // Account number: sub-tag 03/04 by convention, otherwise any long digit string
        accountValues.push(
            ...['04', '03', '05', '02']
                .map(key => sub[key])
                .filter(value => value && /^\+?\d{8,20}$/.test(value)),
        );
    }

    // Layouts differ between wallets: search every sub-field of every template
    // (26-51 account info, 62 additional data, 80-99 unreserved), then the raw payload
    const allValues = [];
    for (const [id, value] of Object.entries(tags)) {
        const numericId = Number(id);
        if ((numericId >= 26 && numericId <= 51) || numericId === 62 || numericId >= 80) {
            allValues.push(...Object.values(parseTlv(value)));
        }
        allValues.push(value);
    }

    details.number = findMobileNumber([...accountValues, ...allValues]);
    details.numberMasked = !details.number && findMaskedMobile(allValues);
    details.bank = details.provider && details.provider !== 'GCash' ? details.provider : null;

    if (details.bank) {
        details.number = accountValues[0]?.replace(/\D/g, '') || details.number;
    }

    const name = (tags['59'] || '').trim();
    if (name) {
        details.nameMasked = name.includes('*');
        details.name = details.nameMasked ? name : toTitleCase(name);
    }

    return details;
}

/** Parse an EMVCo TLV string (2-char id, 2-digit length, value) into { id: value }. */
function parseTlv(payload) {
    const result = {};
    let index = 0;

    while (index + 4 <= payload.length) {
        const id = payload.slice(index, index + 2);
        const length = parseInt(payload.slice(index + 2, index + 4), 10);

        if (Number.isNaN(length)) break;

        result[id] = payload.slice(index + 4, index + 4 + length);
        index += 4 + length;
    }

    return result;
}

function findMobileNumber(values) {
    for (const value of values) {
        const exact = value.replace(/[\s-]/g, '').match(MOBILE_PATTERN);
        if (exact) return '0' + exact[1];

        const embedded = value.match(/(?<!\d)(?:\+?63|0)(9\d{9})(?!\d)/);
        if (embedded) return '0' + embedded[1];
    }

    return null;
}

/** True when the QR only carries a masked mobile number, e.g. 0917***4567. */
function findMaskedMobile(values) {
    return values.some(value => {
        const match = value.match(/(?<![\d*])(?:\+?63|0)9[\d*]{9}(?![\d*])/);
        return Boolean(match && match[0].includes('*'));
    });
}

function toTitleCase(value) {
    return value
        .replace(/[^a-zA-Z\s.]/g, '')
        .replace(/\s+/g, ' ')
        .toLowerCase()
        .replace(/\b[a-z]/g, char => char.toUpperCase());
}
