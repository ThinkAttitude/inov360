import { me } from "../app/api.js";

const KEY = "rh360:user";

function get() {
    const raw = sessionStorage.getItem(KEY);
    if (!raw) return null;
    try {
        return JSON.parse(raw);
    } catch {
        sessionStorage.removeItem(KEY);
        return null;
    }
}

function set(user) {
    if (!user) {
        sessionStorage.removeItem(KEY);
        return null;
    }
    sessionStorage.setItem(KEY, JSON.stringify(user));
    return user;
}

function clear() {
    sessionStorage.removeItem(KEY);
}

async function getPrivileges() {
    const res = await me();
    if (!res?.success || !res?.auth) throw new Error('Failed to fetch user privileges');

    return res.auth;
}

export const User = Object.freeze({
    get,
    set,
    getPrivileges,
    clear,
});
