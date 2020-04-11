export function getlocalStorage(key, _default) {
    let value = window.localStorage.getItem(key);
    if (value !== null) {
        if (value === 'true') return true;
        if (value === 'false') return false;
        return value;
    }
    _default = typeof _default !== 'undefined' ? _default : 1;
    return _default;
}

export function setlocalStorage(key, value) {
    if (typeof value !== 'undefined') {
        window.localStorage.setItem(key, value);
    }
}
