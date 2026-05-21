const PWD_CHARSETS = {
    lower: 'abcdefghijklmnopqrstuvwxyz',
    upper: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    digits: '0123456789',
    symbols: '!@#$%^&*-_=+?<>,.;:|/~()[]{}'
};
const PWD_ALL = PWD_CHARSETS.lower + PWD_CHARSETS.upper + PWD_CHARSETS.digits + PWD_CHARSETS.symbols;
const PWD_LENGTH = 20;

export function secureRandomInt(max) {
    if (window.crypto && typeof window.crypto.getRandomValues === 'function') {
        const limit = Math.floor(0xFFFFFFFF / max) * max;
        const buf = new Uint32Array(1);
        do { window.crypto.getRandomValues(buf); } while (buf[0] >= limit);
        return buf[0] % max;
    }
    return Math.floor(Math.random() * max);
}

function secureShuffle(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
        const j = secureRandomInt(i + 1);
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

export function generateSecurePassword(length = PWD_LENGTH) {
    const chars = [
        PWD_CHARSETS.lower[secureRandomInt(PWD_CHARSETS.lower.length)],
        PWD_CHARSETS.upper[secureRandomInt(PWD_CHARSETS.upper.length)],
        PWD_CHARSETS.digits[secureRandomInt(PWD_CHARSETS.digits.length)],
        PWD_CHARSETS.symbols[secureRandomInt(PWD_CHARSETS.symbols.length)]
    ];
    while (chars.length < length) {
        chars.push(PWD_ALL[secureRandomInt(PWD_ALL.length)]);
    }
    return secureShuffle(chars).join('');
}
