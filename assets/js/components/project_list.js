import $ from 'jquery'
import {
  showDefaultTopBarTitle,
  showCustomTopBarTitle,
} from '../layout/top_bar'

require('../../styles/components/project_list.scss')

export class ProjectList {
  constructor(
    container,
    category,
    apiUrl,
    propertyToShow,
    theme,
    fetchCount = 30,
    emptyMessage = '',
  ) {
    this.container = container
    this.projectsContainer = $('.projects-container', container)
    this.category = category
    this.apiUrl = apiUrl.includes('?') ? apiUrl + '&' : apiUrl + '?'
    this.propertyToShow = propertyToShow
    this.projectsLoaded = 0
    this.projectFetchCount = fetchCount
    this.empty = false
    this.fetchActive = false
    this.isFullView = false
    this.theme = theme
    this.emptyMessage = emptyMessage
    this.$title = $('.project-list__title', $(this.container))
    this.$body = $('body')
    this.$chevronLeft = $('.project-list__chevrons__left', $(this.container))
    this.$chevronRight = $('.project-list__chevrons__right', $(this.container))
    const self = this
    this.popStateHandler = function () {
      self.closeFullView()
    }

    let attributes =
      'id,name,project_url,screenshot_small,screenshot_large,not_for_kids,'
    attributes +=
      this.propertyToShow === 'uploaded'
        ? 'uploaded_string'
        : this.propertyToShow
    this.apiUrl += 'attributes=' + attributes + '&'

    this.fetchMore(true)
    this._initListeners()
  }

  fetchMore(clear = false) {
    if (this.empty === true || this.fetchActive === true) {
      return
    }

    this.fetchActive = true
    const self = this

    $.getJSON(
      this.apiUrl +
        'limit=' +
        this.projectFetchCount +
        '&offset=' +
        this.projectsLoaded,
      function (data) {
        if (!Array.isArray(data)) {
          console.error('Data received for ' + self.category + ' is no array!')
          self.container.classList.remove('loading')
          return
        }

        if (clear) {
          self.projectsContainer.empty()
        }

        data.forEach(function (project) {
          project = self._generate(project)
          self.projectsContainer.append(project)
          project.click(function () {
            project.empty()
            project.css('display', 'flex')
            project.css('justify-content', 'center')
            project.append($('#project-opening-spinner').html())
          })
        })
        self.container.classList.remove('loading')

        if (data.length > 0) {
          self.$chevronRight.show()
        }

        self.projectsLoaded += data.length

        if (self.projectsLoaded === 0 && self.empty === false) {
          self.empty = true
          if (self.emptyMessage) {
            self.projectsContainer.append(self.emptyMessage)
            self.container.classList.add('empty-with-text')
          } else {
            self.container.classList.add('empty')
          }
        }

        self.fetchActive = false
      },
    ).fail(function (jqXHR, textStatus, errorThrown) {
      console.error(
        'Failed loading projects in category ' + self.category,
        JSON.stringify(jqXHR),
        textStatus,
        errorThrown,
      )
      self.container.classList.remove('loading')
    })
  }

  _generate(data) {
    /*
     * Necessary to support legacy flavoring with URL:
     *   Absolute url always uses new 'app' routing flavor. We have to replace it!
     */
    let projectUrl = data.project_url
    projectUrl = projectUrl.replace('/app/', '/' + this.theme + '/')
    //
    const $p = $('<a />', { class: 'project-list__project', href: projectUrl })
    $p.data('id', data.id)

    let style = ''
    if (data.not_for_kids) {
      style = 'filter: blur(10px);'
    }
    $('<img/>', {
      'data-src': data.screenshot_small,
      // TODO: generate larger thumbnails and adapt here (change 80w to width of thumbs)
      'data-srcset':
        data.screenshot_small + ' 80w, ' + data.screenshot_large + ' 480w',
      'data-sizes': '(min-width: 768px) 10vw, 25vw',
      class: 'lazyload project-list__project__image',
      style,
    }).appendTo($p)
    $('<span/>', { class: 'project-list__project__name' })
      .text(data.name)
      .appendTo($p)
    const $prop = $('<div />', {
      class:
        'lazyload project-list__project__property project-list__project__property-' +
        this.propertyToShow,
    })
    $prop.appendTo($p)

    const icons = {
      views: 'visibility',
      downloads: 'get_app',
      uploaded: 'schedule',
      author: 'person',
    }

    const propertyValue =
      this.propertyToShow === 'uploaded'
        ? data.uploaded_string
        : data[this.propertyToShow]
    $('<i/>', { class: 'material-icons' })
      .text(icons[this.propertyToShow])
      .appendTo($prop)
    $('<span/>', { class: 'project-list__project__property__value' })
      .text(propertyValue)
      .appendTo($prop)

    if (data.not_for_kids) {
      const $newProp = $('<div />', {
        class: 'lazyload project-list__project__property__not-for-kids',
      })

      $newProp.appendTo($p)

      $('<img/>', {
        class: 'lazyload project-list__not-for-kids-logo',
        src: '/images/default/not_for_kids.svg',
      }).appendTo($newProp)

      $('<span/>', { class: 'project-list__project__property__value' })
        .text('Not for kids')
        .appendTo($newProp)
    }
    return $p
  }

  _initListeners() {
    const self = this

    // ---- History State
    window.addEventListener('popstate', function (event) {
      if (event.state != null) {
        if (event.state.type === 'ProjectList' && event.state.full === true) {
          $('#' + event.state.id)
            .data('list')
            .openFullView()
        }
      }
    })

    this.projectsContainer.on('scroll', function () {
      const pctHorizontal =
        this.scrollLeft / (this.scrollWidth - this.clientWidth)
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
      const pctVertical =
        this.scrollTop / (this.scrollHeight - this.clientHeight)
      if (pctVertical >= 0.8) {
        self.fetchMore()
      }
    })

    this.$title.on('click', function () {
      if (self.isFullView) {
        window.history.back() // to remove pushed state
      } else {
        self.openFullView()
        window.history.pushState(
          { type: 'ProjectList', id: self.container.id, full: true },
          $(this).text(),
          '#' + self.container.id,
        )
      }
    })

    this.$chevronLeft.on('click', function () {
      const width = self.projectsContainer
        .find('.project-list__project')
        .outerWidth(true)
      self.projectsContainer.scrollLeft(
        self.projectsContainer.scrollLeft() - 2 * width,
      )
    })
    this.$chevronRight.on('click', function () {
      const width = self.projectsContainer
        .find('.project-list__project')
        .outerWidth(true)
      self.projectsContainer.scrollLeft(
        self.projectsContainer.scrollLeft() + 2 * width,
      )
    })
  }

  openFullView() {
    $(window).on('popstate', this.popStateHandler)
    showCustomTopBarTitle(this.$title.find('h2').text(), function () {
      window.history.back()
    })
    this.$title.hide()
    this.isFullView = true
    this.container.classList.add('vertical')
    this.container.classList.remove('horizontal')
    this.$body.addClass('overflow-hidden')
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
