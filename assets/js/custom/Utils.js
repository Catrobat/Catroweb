export function setScopedInterval (func, millis, scope) {
  return setInterval(function () {
    func.apply(scope)
  }, millis)
}
