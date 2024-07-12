import {
  showDefaultTopBarTitle,
  showCustomTopBarTitle,
} from '../layout/top_bar'

import '../../styles/components/user_list.scss'

export class UserList {
  constructor(
    container,
    baseUrl,
    apiUrl,
    theme,
    projectString,
    fetchCount = 30,
    emptyMessage = '',
  ) {
    this.container = container
    this.usersContainer = container.querySelector('.users-container')
    this.apiUrl =
      (apiUrl.includes('?') ? apiUrl + '&' : apiUrl + '?') +
      'attributes=id,username,picture,projects&'
    this.baseUrl = baseUrl
    this.usersLoaded = 0
    this.userFetchCount = fetchCount
    this.empty = false
    this.fetchActive = false
    this.isFullView = false
    this.theme = theme
    this.emptyMessage = emptyMessage
    this.projectString = projectString

    this.titleElement = container.querySelector('.user-list__title')
    this.body = document.body
    this.chevronLeft = container.querySelector('.user-list__chevrons__left')
    this.chevronRight = container.querySelector('.user-list__chevrons__right')
    const self = this
    this.popStateHandler = function () {
      self.closeFullView()
    }

    this.fetchMore(true)
    this._initListeners()
  }

  fetchMore(clear = false) {
    if (this.empty || this.fetchActive) {
      return
    }

    this.fetchActive = true
    const self = this

    fetch(
      this.apiUrl +
        'limit=' +
        this.userFetchCount +
        '&offset=' +
        this.usersLoaded,
    )
      .then((response) => response.json())
      .then((data) => {
        if (!Array.isArray(data)) {
          console.error('Data received for users is no array!')
          self.container.classList.remove('loading')
          return
        }

        if (clear) {
          self.usersContainer.innerHTML = ''
        }

        data.forEach(function (user) {
          user = self._generate(user)
          self.usersContainer.appendChild(user)
          user.addEventListener('click', function () {
            user.innerHTML = ''
            user.style.display = 'flex'
            user.style.justifyContent = 'center'
            user.innerHTML = document.getElementById(
              'user-opening-spinner',
            ).innerHTML
          })
        })
        self.container.classList.remove('loading')

        if (data.length > 0) {
          self.chevronRight.style.display = 'block'
        }

        self.usersLoaded += data.length

        if (self.usersLoaded === 0 && !self.empty) {
          self.empty = true
          if (self.emptyMessage) {
            self.usersContainer.innerHTML = self.emptyMessage
            self.container.classList.add('empty-with-text')
          } else {
            self.container.classList.add('empty')
          }
        }

        self.fetchActive = false
      })
      .catch((jqXHR, textStatus, errorThrown) => {
        console.error(
          'Failed loading users',
          JSON.stringify(jqXHR),
          textStatus,
          errorThrown,
        )
        self.container.classList.remove('loading')
      })
  }

  _generate(data) {
    const userUrl = this.baseUrl + '/app/user/' + data.id

    const userElement = document.createElement('a')
    userElement.className = 'user-list__user'
    userElement.href = userUrl
    userElement.dataset.id = data.id

    const userImage = document.createElement('img')
    userImage.className = 'user-list__user__image'
    if (typeof data.picture === 'string' && data.picture.length > 0) {
      userImage.src = data.picture
    } else {
      userImage.dataset.src = '/images/default/avatar_default.png?v=3.7.1'
      userImage.classList.add('lazyload')
    }
    userElement.appendChild(userImage)

    const userName = document.createElement('span')
    userName.className = 'user-list__user__name'
    userName.textContent = data.username
    userElement.appendChild(userName)

    const userProperty = document.createElement('div')
    userProperty.className = 'lazyload user-list__user__property'
    userElement.appendChild(userProperty)

    const userPropertyValue = document.createElement('span')
    userPropertyValue.className = 'user-list__user__property__value'
    userPropertyValue.textContent = data.projects + ' ' + this.projectString
    userProperty.appendChild(userPropertyValue)

    return userElement
  }

  _initListeners() {
    const self = this

    window.addEventListener('popstate', function (event) {
      if (event.state != null) {
        if (event.state.type === 'UserList' && event.state.full === true) {
          document.getElementById(event.state.id).dataset.list.openFullView()
        }
      }
    })

    this.usersContainer.addEventListener('scroll', function () {
      const pctHorizontal =
        this.scrollLeft / (this.scrollWidth - this.clientWidth)
      if (pctHorizontal >= 0.8) {
        self.fetchMore()
      }
      self.chevronLeft.style.display = pctHorizontal === 0 ? 'none' : 'block'
      self.chevronRight.style.display = pctHorizontal >= 1 ? 'none' : 'block'
    })

    this.container.addEventListener('scroll', function () {
      const pctVertical =
        this.scrollTop / (this.scrollHeight - this.clientHeight)
      if (pctVertical >= 0.8) {
        self.fetchMore()
      }
    })

    this.titleElement.addEventListener('click', function () {
      if (self.isFullView) {
        window.history.back()
      } else {
        window.history.pushState(
          { type: 'UserList', id: self.container.id, full: true },
          this.textContent,
          '#' + self.container.id,
        )
        self.openFullView()
      }
    })

    this.chevronLeft.addEventListener('click', function () {
      const width = self.usersContainer.querySelector(
        '.user-list__project',
      ).offsetWidth
      self.usersContainer.scrollLeft -= 2 * width
    })

    this.chevronRight.addEventListener('click', function () {
      const width = self.usersContainer.querySelector(
        '.user-list__project',
      ).offsetWidth
      self.usersContainer.scrollLeft += 2 * width
    })
  }

  openFullView() {
    window.addEventListener('popstate', this.popStateHandler)
    showCustomTopBarTitle(
      this.titleElement.querySelector('h2').textContent,
      function () {
        window.history.back()
      },
    )
    this.titleElement.style.display = 'none'
    this.isFullView = true
    this.container.classList.add('vertical')
    this.container.classList.remove('horizontal')
    this.body.classList.add('overflow-hidden')
    if (
      this.container.clientHeight === this.container.scrollHeight ||
      this.container.scrollTop /
        (this.container.scrollHeight - this.container.clientHeight) >=
        0.8
    ) {
      this.fetchMore()
    }
  }

  closeFullView() {
    window.removeEventListener('popstate', this.popStateHandler)
    showDefaultTopBarTitle()
    this.titleElement.style.display = 'block'
    this.isFullView = false
    this.container.classList.add('horizontal')
    this.container.classList.remove('vertical')
    this.body.classList.remove('overflow-hidden')
    return false
  }
}
