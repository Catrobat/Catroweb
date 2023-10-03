module.exports = function (Handlebars) {
  Handlebars.registerHelper('custom', function (context, options) {
    if (!context || context.length === 0) {
      return ''
    }

    const list = context
      .filter((item) => {
        const commit = item.commit || item
        if (options.hash.exclude) {
          const pattern = new RegExp(options.hash.exclude, 'i')
          if (pattern.test(commit.message)) {
            return false
          }
        }
        if (options.hash.message) {
          const pattern = new RegExp(options.hash.message, 'i')
          return pattern.test(commit.message)
        }
        if (options.hash.subject) {
          const pattern = new RegExp(options.hash.subject)
          return pattern.test(commit.subject)
        }
        return true
      })
      .map((item) => options.fn(item))
      .join('')

    if (!list) {
      return ''
    }

    return `${options.hash.heading}\n\n${list}`
  })
}
