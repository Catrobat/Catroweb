/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
class ProjectList {
  constructor (container, category, apiUrl, propertyToShow, performClickStatisticRequest) {
    this.container = container
    this.projectsContainer = $('.projects-container', container)
    this.category = category
    this.apiUrl = apiUrl
    this.propertyToShow = propertyToShow
    this.projectsLoaded = 0
    this.projectFetchCount = 30
    this.empty = false
    this.fetchActive = false
    this.isFullView = false
    this.performClickStatisticRequest = performClickStatisticRequest

    this.$title = $('.project-list__title', $(this.container))
    this.$body = $('body')
    this.$chevronLeft = $('.project-list__chevrons__left', $(this.container))
    this.$chevronRight = $('.project-list__chevrons__right', $(this.container))
    const self = this
    this.popStateHandler = function () {
      self.closeFullView()
    }

    this.fetchMore()
    this._initListeners()
  }

  fetchMore () {
    if (this.empty === true || this.fetchActive === true) {
      return
    }

    this.fetchActive = true
    const self = this
    $.getJSON(this.apiUrl + '&limit=' + this.projectFetchCount + '&offset=' + this.projectsLoaded,
      function (data) {
        if (!Array.isArray(data)) {
          console.error('Data received for ' + self.category + ' is no array!')
          alert('Server Error: Failed loading ' + self.category.replace('_', ' ') + ' projects')
          self.container.classList.remove('loading')
          return
        }
        data.forEach(function (project) {
          project = self._generate(project)
          self.projectsContainer.append(project)
          project.click(function () {
            project.append($('#project-opening-spinner').html())
            const href = $(this).attr('href')
            const programID = ((href.indexOf('project') > 0) ? (href.split('project/')[1]).split('?')[0] : 0)
            const type = self.getClickStatisticType(self.category)
            const userSpecificRecommendation = type === 'user_who_downloaded_also_downloaded'
            self.performClickStatisticRequest(href, type[0], type[1], userSpecificRecommendation, programID)
          })
        })
        self.container.classList.remove('loading')

        if (data.length > 0) {
          self.$chevronRight.show()
        }

        self.projectsLoaded += data.length

        if (self.projectsLoaded === 0) {
          self.container.classList.add('empty')
          this.empty = true
        }

        self.fetchActive = false
      }).fail(function (jqXHR, textStatus, errorThrown) {
      console.error('Failed loading projects in category ' + self.category, JSON.stringify(jqXHR), textStatus, errorThrown)
      alert('Error: Failed loading ' + self.category.replace('_', ' ') + ' projects')
      self.container.classList.remove('loading')
    })
  }

  _generate (data) {
    const $p = $('<a />', { class: 'project-list__project', href: data.project_url })
    $p.data('id', data.id)
    $('<img/>', {
      src: data.screenshot_small,
      // TODO: generate larger thumbnails and adapt here (change 80w to width of thumbs)
      srcset: data.screenshot_small + ' 80w, ' + data.screenshot_large + ' 480w',
      sizes: '(min-width: 768px) 10vw, 25vw',
      alt: '',
      class: 'project-list__project__image'
    }).appendTo($p)
    $('<span/>', { class: 'project-list__project__name' }).text(data.name).appendTo($p)
    const $prop = $('<div />', { class: 'project-list__project__property project-list__project__property-' + this.propertyToShow })
    $prop.appendTo($p)

    const icons = {
      views: 'visibility',
      downloads: 'get_app',
      uploaded: 'schedule',
      author: 'person'
    }

    const propertyValue = this.propertyToShow === 'downloads' ? data.download
      : this.propertyToShow === 'uploaded' ? data.uploaded_string : data[this.propertyToShow]
    $('<i/>', { class: 'material-icons' }).text(icons[this.propertyToShow]).appendTo($prop)
    $('<span/>', { class: 'project-list__project__property__value' }).text(propertyValue).appendTo($prop)
    return $p
  }

  _initListeners () {
    const self = this
    this.projectsContainer.on('scroll', function () {
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
        history.pushState(
          { type: 'ProjectList', id: self.container.id, full: true },
          $(this).text(), '#' + self.container.id
        )
        self.openFullView()
      }
    })

    this.$chevronLeft.on('click', function () {
      const width = self.projectsContainer.find('.project-list__project').outerWidth(true)
      self.projectsContainer.scrollLeft(self.projectsContainer.scrollLeft() - 2 * width)
    })
    this.$chevronRight.on('click', function () {
      const width = self.projectsContainer.find('.project-list__project').outerWidth(true)
      self.projectsContainer.scrollLeft(self.projectsContainer.scrollLeft() + 2 * width)
    })
  }

  openFullView () {
    $(window).on('popstate', this.popStateHandler)
    // eslint-disable-next-line no-undef
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
    // eslint-disable-next-line no-undef
    showDefaultTopBarTitle()
    this.$title.show()
    this.isFullView = false
    this.container.classList.add('horizontal')
    this.container.classList.remove('vertical')
    this.$body.removeClass('overflow-hidden')
    return false
  }

  getClickStatisticType (type) {
    switch (type) {
      case 'recent':
        return ['newest', false]
      case 'most_downloaded':
        return ['mostDownloaded', false]
      case 'most_viewed':
        return ['mostViewed', false]
      case 'scratch':
        return ['scratchRemixes', false]
      case 'recommended':
        return ['rec_homepage', true]
      case 'similar':
        return ['project', true]
      case 'user_who_downloaded_also_downloaded':
        return ['rec_specific_programs', true]
      default:
        return [type, false]
    }
  }
}
