import $ from 'jquery'

class SearchList {
    constructor (container, category, url, query, propertyToShow, theme) {
        const self = this

        this.container = container
        this.projectsContainer = $('.projects-container')
        this.usersContainer = $('.users-container')
        this.category = category

        this.projectsUrl = url + 'projects/search?query=' + query
        this.usersUrl = url + 'users/search?query=' + query

        this.query = query
        this.propertyToShow = propertyToShow
        this.fetchCount = 30
        this.projectsOffset = 0
        this.usersOffset = 0
        this.totalProjects = 0
        this.totalUsers = 0
        this.total = 0

        this.empty = false

        this.fetchActiveProjects = false
        this.fetchActiveUsers = false
        this.isFullView = false

        this.theme = theme

        this.$title = $('.search-results__title', $(this.container))
        this.$body = $('body')
        this.popStateHandler = function () {
             self.closeFullView()
        }

        this.fetchProjects()
        this.fetchUsers()
        this._initListeners()
    }

    fetchProjects () {
        if (this.empty === true || this.fetchActiveProjects === true) {
            return
        }

        this.fetchActiveProjects = true
        const self = this
        $.getJSON(this.projectsUrl + '&limit=' + this.fetchCount + '&offset=' + this.projectsOffset,
            function (data) {
                console.log(data)
                self.totalProjects = data.total
                if (!Array.isArray(data.projects)) {
                    console.error('Data received for ' + self.category + ' is no array!')
                    alert('Server Error: Failed loading ' + self.category.replace('_', ' ') + ' projects')
                    self.projectsContainer.removeClass('loading')
                    return
                }
                data.projects.forEach(function (project) {
                    project = self._generateProject(project)
                    self.projectsContainer.append(project)
                    project.click(function () {
                        project.empty()
                        project.css('display','flex')
                        project.css('justify-content','center')
                        project.append($('#project-opening-spinner').html())
                    })
                })

                self.projectsContainer.removeClass('loading')

                self.projectsLoaded += data.projects.length

                if (self.projectsLoaded === 0) {
                    self.projectsContainer.addClass('empty')
                    this.empty = true
                }

                self.fetchActiveProjects = false
            }).fail(function (jqXHR, textStatus, errorThrown) {
            console.error('Failed loading projects in category ' + self.category, JSON.stringify(jqXHR), textStatus, errorThrown)
            alert('Error: Failed loading ' + self.category.replace('_', ' ') + ' projects')
            self.projectsContainer.removeClass('loading')
        })
    }

    fetchUsers () {
        if (this.empty === true || this.fetchActiveUsers === true) {
            return
        }

        this.fetchActiveUsers = true
        const self = this
        $.getJSON(this.usersUrl + '&limit=' + this.fetchCount + '&offset=' + this.usersOffset,
            function (data) {
                console.log(data)
                self.totalUsers = data.total

                if (!Array.isArray(data.users)) {
                    console.error('Data received for ' + self.category + ' is no array!')
                    alert('Server Error: Failed loading users for search ' + self.query)
                    self.usersContainer.removeClass('loading')
                    return
                }

                data.users.forEach(function (user) {
                    user = self._generateUser(user)
                    self.usersContainer.append(user)
                })

                self.usersContainer.removeClass('loading')

                self.usersLoaded += data.users.length

                if (self.usersLoaded === 0) {
                    self.usersContainer.addClass('empty')
                    this.empty = true
                }

                self.fetchActiveUsers = false
            }).fail(function (jqXHR, textStatus, errorThrown) {
            console.error('Failed loading projects in category ' + self.category, JSON.stringify(jqXHR), textStatus, errorThrown)
            alert('Error: Failed loading ' + self.category.replace('_', ' ') + ' projects')
            self.usersContainer.removeClass('loading')
        })
    }

    _generateProject (data) {
        /*
        * Necessary to support legacy flavoring with URL:
        *   Absolute url always uses new 'app' routing flavor. We have to replace it!
        */
        let projectUrl = data.project_url
        projectUrl = projectUrl.replace('/app/', '/' + this.theme + '/')
        //

        const $p = $('<a />', { class: 'project-list__project', href: projectUrl })
        $p.data('id', data.id)
        $('<img/>', {
            'data-src': data.screenshot_small,
            // TODO: generate larger thumbnails and adapt here (change 80w to width of thumbs)
            'data-srcset': data.screenshot_small + ' 80w, ' + data.screenshot_large + ' 480w',
            'data-sizes': '(min-width: 768px) 10vw, 25vw',
            class: 'lazyload project-list__project__image'
        }).appendTo($p)
        $('<span/>', { class: 'project-list__project__name' }).text(data.name).appendTo($p)
        const $prop = $('<div />', { class: 'lazyload project-list__project__property project-list__project__property-' + this.propertyToShow })
        $prop.appendTo($p)

        const icons = {
            views: 'visibility',
            downloads: 'get_app',
            uploaded: 'schedule',
            author: 'person'
        }

        const propertyValue = this.propertyToShow === 'downloads' ? data.download : this.propertyToShow === 'uploaded' ? data.uploaded_string : data[this.propertyToShow]
        $('<i/>', { class: 'material-icons' }).text(icons[this.propertyToShow]).appendTo($prop)
        $('<span/>', { class: 'project-list__project__property__value' }).text(propertyValue).appendTo($prop)
        return $p
    }

    _generateUser (data) {
        /*
            * Necessary to support legacy flavoring with URL:
            *   Absolute url always uses new 'app' routing flavor. We have to replace it!
            */
        let userUrl = ''
        userUrl = userUrl.replace('/app/', '/' + this.theme + '/')
        //

        const $p = $('<a />', { class: 'user-list__user', href: userUrl })
        $p.data('id', data.id)
        $('<img/>', {
            'data-src': '/images/default/avatar_default.png?v=3.7.1',
            // TODO: generate larger thumbnails and adapt here (change 80w to width of thumbs)
            // 'data-srcset': data.screenshot_small + ' 80w, ' + data.screenshot_large + ' 480w',
            'data-sizes': '(min-width: 768px) 10vw, 25vw',
            class: 'lazyload user-list__user__image'
        }).appendTo($p)
        $('<span/>', { class: 'user-list__user__name' }).text(data.username).appendTo($p)
        const $prop = $('<div />', { class: 'lazyload user-list__user__property user-list__user__property-' + this.propertyToShow })
        $prop.appendTo($p)

        const propertyValue = data.projects + ' label projects'
        $('<span/>', { class: 'user-list__user__property__value' }).text(propertyValue).appendTo($prop)
        return $p
    }

    _initListeners () {
        const self = this
        this.projectsContainer.on('scroll', function () {
            const pctHorizontal = this.scrollLeft / (this.scrollWidth - this.clientWidth)
            if (pctHorizontal >= 0.8) {
                console.log('todo fetch more')
            }

        })
        $(this.container).on('scroll', function () {
            const pctVertical = this.scrollTop / (this.scrollHeight - this.clientHeight)
            if (pctVertical >= 0.8) {
                console.log('todo fetch more')
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
                // self.openFullView()
            }
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
            console.log('todo fetch more')
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
