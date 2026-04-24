import {me} from "../app/api.js";

const listeners = new Set();

let currentAuth = null;
let pendingAuth = null;

function emit(auth = currentAuth) {
    listeners.forEach(listener => listener(auth));
    return auth;
}

function get() {
    return currentAuth;
}

function set(auth) {
    currentAuth = auth || null;
    return emit(currentAuth);
}

function clear() {
    currentAuth = null;
    pendingAuth = null;
    return emit(null);
}

function subscribe(listener) {
    listeners.add(listener);
    return () => listeners.delete(listener);
}

async function refresh() {
    if (pendingAuth) return pendingAuth;

    pendingAuth = me()
        .then(res => {
            if (!res?.success || !res?.auth) return clear();
            return set(res.auth);
        })
        .catch(err => {
            clear();
            throw err;
        })
        .finally(() => {
            pendingAuth = null;
        });

    return pendingAuth;
}

async function ensure(options = {}) {
    if (options.force || !currentAuth) return refresh();
    return currentAuth;
}

async function getPrivileges() {
    const auth = await ensure({force: true});
    if (!auth) throw new Error("UNAUTHENTICATED");
    return auth;
}

export const User = Object.freeze({
    get,
    set,
    clear,
    subscribe,
    refresh,
    ensure,
    getPrivileges,
});