import $ from 'jquery'
import { showDefaultTopBarTitle, showCustomTopBarTitle } from '../layout/top_bar'

require('../../styles/components/user_list.scss')

export class UserList {
  constructor (container, baseUrl, apiUrl, theme, projectString, fetchCount = 30, emptyMessage = '') {
    this.container = container
    this.usersContainer = $('.users-container', container)
    this.apiUrl = (apiUrl.includes('?') ? apiUrl + '&' : apiUrl + '?') + 'attributes=id,username,picture,projects&'
    this.baseUrl = baseUrl
    this.usersLoaded = 0
    this.userFetchCount = fetchCount
    this.empty = false
    this.fetchActive = false
    this.isFullView = false
    this.theme = theme
    this.emptyMessage = emptyMessage
    this.projectString = projectString

    this.$title = $('.user-list__title', $(this.container))
    this.$body = $('body')
    this.$chevronLeft = $('.user-list__chevrons__left', $(this.container))
    this.$chevronRight = $('.user-list__chevrons__right', $(this.container))
    const self = this
    this.popStateHandler = function () {
      self.closeFullView()
    }

    this.fetchMore(true)
    this._initListeners()
  }

  fetchMore (clear = false) {
    if (this.empty === true || this.fetchActive === true) {
      return
    }

    this.fetchActive = true
    const self = this

    $.getJSON(this.apiUrl + 'limit=' + this.userFetchCount + '&offset=' + this.usersLoaded,
      function (data) {
        if (!Array.isArray(data)) {
          console.error('Data received for users is no array!')
          self.container.classList.remove('loading')
          return
        }

        if (clear) {
          self.usersContainer.empty()
        }

        data.forEach(function (user) {
          user = self._generate(user)
          self.usersContainer.append(user)
          user.click(function () {
            user.empty()
            user.css('display', 'flex')
            user.css('justify-content', 'center')
            user.append($('#user-opening-spinner').html())
          })
        })
        self.container.classList.remove('loading')

        if (data.length > 0) {
          self.$chevronRight.show()
        }

        self.usersLoaded += data.length

        if (self.usersLoaded === 0 && self.empty === false) {
          self.empty = true
          if (self.emptyMessage) {
            self.usersContainer.append(self.emptyMessage)
            self.container.classList.add('empty-with-text')
          } else {
            self.container.classList.add('empty')
          }
        }

        self.fetchActive = false
      }).fail(function (jqXHR, textStatus, errorThrown) {
      console.error('Failed loading users', JSON.stringify(jqXHR), textStatus, errorThrown)
      self.container.classList.remove('loading')
    })
  }

  _generate (data) {
    /*
        * Necessary to support legacy flavoring with URL:
        *   Absolute url always uses new 'app' routing flavor. We have to replace it!
        */
    const userUrl = this.baseUrl + '/app/user/' + data.id

    const $p = $('<a />', { class: 'user-list__user', href: userUrl })
    $p.data('id', data.id)
    if (typeof data.picture === 'string' && data.picture.length > 0) {
      $('<img />', {
        src: data.picture,
        class: 'user-list__user__image'
      }).appendTo($p)
    } else {
      $('<img/>', {
        'data-src': '/images/default/avatar_default.png?v=3.7.1',
        class: 'lazyload user-list__user__image'
      }).appendTo($p)
    }
    $('<span/>', { class: 'user-list__user__name' }).text(data.username).appendTo($p)
    const $prop = $('<div />', { class: 'lazyload user-list__user__property' })
    $prop.appendTo($p)
    $('<span/>', { class: 'user-list__user__property__value' }).text(data.projects + ' ' + this.projectString).appendTo($prop)

    return $p
  }

  _initListeners () {
    const self = this

    // ---- History State
    window.addEventListener('popstate', function (event) {
      if (event.state != null) {
        if (event.state.type === 'UserList' && event.state.full === true) {
          $('#' + event.state.id).data('list').openFullView()
        }
      }
    })

    this.usersContainer.on('scroll', function () {
      const pctHorizontal = this.scrollLeft / (this.scrollWidth - this.clientWidth)
      if (pctHorizontal >= 0.8) {
        self.fetchMore()
      }
      if (pctHorizontal === 0) {
        self.$chevronLeft.hide()
      } else {
        self.$chevronLeft.show()
      }

      if (pctHorizontal >= 1) {
        self.$chevronRight.hide()
      } else {
        self.$chevronRight.show()
      }
    })
    $(this.container).on('scroll', function () {
      const pctVertical = this.scrollTop / (this.scrollHeight - this.clientHeight)
      if (pctVertical >= 0.8) {
        self.fetchMore()
      }
    })

    this.$title.on('click', function () {
      if (self.isFullView) {
        window.history.back() // to remove pushed state
      } else {
        window.history.pushState(
          { type: 'UserList', id: self.container.id, full: true },
          $(this).text(), '#' + self.container.id
        )
        self.openFullView()
      }
    })

    this.$chevronLeft.on('click', function () {
      const width = self.usersContainer.find('.user-list__project').outerWidth(true)
      self.usersContainer.scrollLeft(self.usersContainer.scrollLeft() - 2 * width)
    })
    this.$chevronRight.on('click', function () {
      const width = self.usersContainer.find('.user-list__project').outerWidth(true)
      self.usersContainer.scrollLeft(self.usersContainer.scrollLeft() + 2 * width)
    })
  }

  openFullView () {
    $(window).on('popstate', this.popStateHandler)
    showCustomTopBarTitle(this.$title.find('h2').text(), function () {
      window.history.back()
    })
    this.$title.hide()
    this.isFullView = true
    this.container.classList.add('vertical')
    this.container.classList.remove('horizontal')
    this.$body.addClass('overflow-hidden')
    if (this.container.clientHeight === this.container.scrollHeight || this.container.scrollTop / (this.container.scrollHeight - this.container.clientHeight) >= 0.8) {
      this.fetchMore()
    }
  }

  closeFullView () {
    $(window).off('popstate', this.popStateHandler)
    showDefaultTopBarTitle()
    this.$title.show()
    this.isFullView = false
    this.container.classList.add('horizontal')
    this.container.classList.remove('vertical')
    this.$body.removeClass('overflow-hidden')
    return false
  }
}
