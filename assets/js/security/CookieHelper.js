export function deleteCookie(cname, path) {
  document.cookie =
    cname +
    '=;' +
    'expires=Thu, 01 Jan 1970 00:00:01 GMT;' +
    'path=' +
    (path && path !== '' ? path : '/') +
    ';'
}

export function getCookie(cname) {
  const name = cname + '='
  const decodedCookie = decodeURIComponent(document.cookie)
  const ca = decodedCookie.split(';')
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i]
    while (c.charAt(0) === ' ') {
      c = c.substring(1)
    }
    if (c.indexOf(name) === 0) {
      return c.substring(name.length, c.length)
    }
  }
  return null
}

export function setCookie(name, value, expires, path) {
  let cookie = name + '=' + value
  if (expires) {
    cookie += ';expires=' + expires
  }
  cookie += ';path=' + (path && path !== '' ? path : '/')
  document.cookie = cookie
}
