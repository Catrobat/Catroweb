import { Controller } from '@hotwired/stimulus'
import { showSnackbar, SnackbarDuration } from '../../Layout/Snackbar'
import { escapeHtml, escapeAttr } from '../../Components/HtmlEscape'
import { shareOrCopy } from '../../Components/ClipboardHelper'
import Swal from 'sweetalert2'

export default class extends Controller {
  static values = {
    studioId: String,
    projectsUrl: String,
    addProjectUrl: String,
    batchAddProjectsUrl: String,
    removeProjectUrl: String,
    userRole: String,
    isLoggedIn: Boolean,
  }

  static targets = ['container', 'loadMore', 'count', 'noProjects', 'description']

  cursor = null
  hasMore = false

  connect() {
    this._boundContainerClick = this._handleContainerClick.bind(this)
    this._boundOutsideClick = (e) => {
      if (!this.element.contains(e.target)) {
        this._closeAllDropdowns()
      }
    }
    this.loadProjects()
    this.element.addEventListener('click', this._boundContainerClick)
    document.addEventListener('click', this._boundOutsideClick)
  }

  disconnect() {
    this.element.removeEventListener('click', this._boundContainerClick)
    document.removeEventListener('click', this._boundOutsideClick)
  }

  async loadProjects() {
    const url = new URL(this.projectsUrlValue, window.location.origin)
    url.searchParams.set('limit', '20')
    if (this.cursor) {
      url.searchParams.set('cursor', this.cursor)
    }

    try {
      const response = await fetch(url, { credentials: 'same-origin' })
      if (!response.ok) {
        return
      }

      const data = await response.json()
      this.hasMore = data.has_more
      this.cursor = data.next_cursor

      if (data.data && data.data.length > 0) {
        this.noProjectsTarget.style.display = 'none'
        data.data.forEach((project) => this.renderProject(project))
      } else if (!this.cursor) {
        this.noProjectsTarget.style.display = 'block'
      }

      this.loadMoreTarget.style.display = this.hasMore ? 'block' : 'none'
    } catch (e) {
      console.error('Failed to load projects:', e)
    }
  }

  renderProject(project) {
    const canRemove =
      this.userRoleValue === 'admin' || (this.isLoggedInValue && this.userRoleValue === 'member')

    const card = document.createElement('div')
    card.className = 'studio-project-card'
    card.dataset.projectId = project.id

    const projectUrl = '/app/project/' + escapeAttr(String(project.id))
    const screenshotUrl = project.screenshot_small || '/images/default/screenshot.png'
    const addedDate = project.added_at ? new Date(project.added_at).toLocaleDateString() : ''

    const transOpen = this.element.dataset.transOpenProject || 'Open project'
    const transDownload = this.element.dataset.transDownload || 'Download'
    const transShare = this.element.dataset.transShare || 'Share'
    const transRemove = this.element.dataset.transRemoveFromStudio || 'Remove from studio'

    let menuItems = `
      <a href="${projectUrl}" class="projects-list-item--dropdown-item" data-menu-action="open" data-project-id="${escapeAttr(String(project.id))}">
        <i class="material-icons">open_in_new</i>${escapeHtml(transOpen)}
      </a>
      <a href="/api/project/${escapeAttr(String(project.id))}/catrobat${project.name ? '?fname=' + encodeURIComponent(project.name) : ''}" download class="projects-list-item--dropdown-item" data-menu-action="download" data-project-id="${escapeAttr(String(project.id))}" data-project-name="${escapeAttr(project.name || '')}">
        <i class="material-icons">download</i>${escapeHtml(transDownload)}
      </a>
      <button class="projects-list-item--dropdown-item" data-menu-action="share" data-project-id="${escapeAttr(String(project.id))}">
        <i class="material-icons">share</i>${escapeHtml(transShare)}
      </button>`

    if (canRemove) {
      menuItems += `
      <div class="projects-list-item--dropdown-divider"></div>
      <button class="projects-list-item--dropdown-item text-danger" data-menu-action="remove" data-project-id="${escapeAttr(String(project.id))}">
        <i class="material-icons">delete</i>${escapeHtml(transRemove)}
      </button>`
    }

    card.innerHTML = `
      <a href="${projectUrl}" class="studio-project-card__screenshot">
        <img src="${escapeAttr(screenshotUrl)}" class="studio-project-card__image" alt="${escapeAttr(project.name || '')}" loading="lazy">
      </a>
      <div class="studio-project-card__info">
        <a href="${projectUrl}" class="studio-project-card__name">${escapeHtml(project.name || '')}</a>
        <div class="studio-project-card__meta">
          ${project.author_id ? `<a href="/app/user/${escapeAttr(String(project.author_id))}" class="studio-project-card__author">${escapeHtml(project.author || project.added_by || '')}</a>` : `<span class="studio-project-card__author">${escapeHtml(project.author || project.added_by || '')}</span>`}
          ${addedDate ? '<span class="studio-project-card__date">' + escapeHtml(addedDate) + '</span>' : ''}
        </div>
      </div>
      <div class="projects-list-item--actions">
        <button class="btn projects-list-item--menu-btn" type="button" aria-label="Options" data-project-id="${escapeAttr(String(project.id))}">
          <i class="material-icons">more_vert</i>
        </button>
        <div class="projects-list-item--dropdown" style="display:none;">
          ${menuItems}
        </div>
      </div>
    `
    this.containerTarget.appendChild(card)
  }

  loadMore() {
    if (this.hasMore) {
      this.loadProjects()
    }
  }

  _handleContainerClick(event) {
    const menuBtn = event.target.closest('.projects-list-item--menu-btn')
    if (menuBtn) {
      event.preventDefault()
      event.stopPropagation()
      this._toggleDropdown(menuBtn)
      return
    }

    const menuItem = event.target.closest('[data-menu-action]')
    if (menuItem) {
      const dropdown = menuItem.closest('.projects-list-item--dropdown')
      if (dropdown) {
        dropdown.style.display = 'none'
      }
      this._handleMenuAction(menuItem)
      return
    }

    // Close all dropdowns on outside click
    this._closeAllDropdowns()
  }

  _toggleDropdown(button) {
    const dropdown = button.nextElementSibling
    const isOpen = dropdown.style.display !== 'none'
    this._closeAllDropdowns()
    dropdown.style.display = isOpen ? 'none' : 'block'
  }

  _closeAllDropdowns() {
    this.element.querySelectorAll('.projects-list-item--dropdown').forEach((d) => {
      d.style.display = 'none'
    })
  }

  _handleMenuAction(item) {
    const action = item.dataset.menuAction
    const projectId = item.dataset.projectId

    switch (action) {
      case 'share':
        this._shareProject(projectId)
        break
      case 'remove':
        this.confirmRemoveProject(projectId)
        break
      // 'open' and 'download' are <a> tags — they navigate natively
    }
  }

  _shareProject(projectId) {
    const projectUrl = window.location.origin + '/app/project/' + projectId
    const msg = this.element.dataset.transShareSuccess || 'Link copied!'
    shareOrCopy(projectUrl, () => showSnackbar('#share-snackbar', msg))
  }

  async confirmRemoveProject(projectId) {
    const result = await Swal.fire({
      title: this.element.dataset.transAreYouSure || 'Are you sure?',
      text: this.element.dataset.transNoWayOfReturn || 'This cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
      confirmButtonText: this.element.dataset.transDeleteIt || 'Delete',
      cancelButtonText: this.element.dataset.transCancel || 'Cancel',
    })

    if (result.isConfirmed) {
      await this.removeProject(projectId)
    }
  }

  async openAddProjectModal() {
    try {
      // Derive base from projectsUrl (e.g., "/index_test.php/api/studio/1/projects" -> "/index_test.php/api/studio/1")
      const studioBase = this.projectsUrlValue.replace(/\/projects$/, '')
      const url = studioBase + '/user-projects'
      const response = await fetch(url, { credentials: 'same-origin' })
      if (!response.ok) {
        return
      }

      const data = await response.json()
      const available = data.projects.filter((p) => !p.in_studio)

      if (available.length === 0) {
        const emptyMsg =
          this.element.dataset.transNoProjectsEmptyState ||
          "You don't have any projects to add yet. Create a project in Pocket Code first!"

        await Swal.fire({
          title: this.element.dataset.transAddProject || 'Add Projects',
          html: `<div class="text-center py-3">
            <span class="material-icons" style="font-size: 48px; color: #9e9e9e;">inventory_2</span>
            <p class="mt-3 mb-0">${escapeHtml(emptyMsg)}</p>
          </div>`,
          customClass: {
            confirmButton: 'btn btn-primary',
          },
          buttonsStyling: false,
          confirmButtonText: 'OK',
          showCancelButton: false,
        })
        return
      }

      const defaultScreenshot = '/images/default/screenshot.png'
      let html =
        '<div class="studio-add-project-list" style="max-height: 400px; overflow-y: auto;">'
      available.forEach((p) => {
        const screenshot = p.screenshot_small || defaultScreenshot
        html += `<label class="studio-add-project-item" for="add-project-${escapeAttr(String(p.id))}"
          style="display: flex; align-items: center; gap: 12px; padding: 8px 12px; margin: 0; cursor: pointer; border-bottom: 1px solid #eee; transition: background-color 0.15s;">
          <input class="form-check-input mt-0" type="checkbox" value="${escapeAttr(String(p.id))}" id="add-project-${escapeAttr(String(p.id))}"
            style="flex-shrink: 0;">
          <img src="${escapeAttr(screenshot)}" alt="" width="40" height="40"
            style="border-radius: 4px; object-fit: cover; flex-shrink: 0;">
          <span style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: left;">
            ${escapeHtml(p.name)}
          </span>
        </label>`
      })
      html += '</div>'

      const addSelectedText = this.element.dataset.transAddSelected || 'Add Selected'

      const result = await Swal.fire({
        title: this.element.dataset.transAddProject || 'Add Projects',
        html: html,
        showCancelButton: true,
        customClass: {
          confirmButton: 'btn btn-primary',
          cancelButton: 'btn btn-outline-primary',
        },
        buttonsStyling: false,
        confirmButtonText: addSelectedText,
        cancelButtonText: this.element.dataset.transCancel || 'Cancel',
        didOpen: () => {
          const container = Swal.getHtmlContainer()
          if (container) {
            container.addEventListener('change', (e) => {
              if (e.target.matches('.form-check-input')) {
                const item = e.target.closest('.studio-add-project-item')
                if (item) {
                  item.style.backgroundColor = e.target.checked ? '#e0f2f1' : ''
                }
              }
            })
          }
        },
        preConfirm: () => {
          const checked = document.querySelectorAll('.studio-add-project-list input:checked')
          return Array.from(checked).map((c) => c.value)
        },
      })

      if (result.isConfirmed && result.value.length > 0) {
        await this.batchAddProjects(result.value)
        this.containerTarget.innerHTML = ''
        this.cursor = null
        this.loadProjects()
        this.updateCount()
      }
    } catch (e) {
      console.error('Failed to open add project modal:', e)
    }
  }

  async addProject(projectId) {
    const url = this.addProjectUrlValue
    try {
      await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ project_id: projectId }),
      })
    } catch (e) {
      console.error('Failed to add project:', e)
    }
  }

  async batchAddProjects(projectIds) {
    const url = this.batchAddProjectsUrlValue
    try {
      const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ project_ids: projectIds }),
      })
      if (!response.ok) {
        showSnackbar(
          '#share-snackbar',
          this.element.dataset.transAddError || 'Failed to add projects.',
          SnackbarDuration.error,
        )
      }
    } catch (e) {
      console.error('Failed to batch add projects:', e)
      showSnackbar(
        '#share-snackbar',
        this.element.dataset.transAddError || 'Failed to add projects.',
        SnackbarDuration.error,
      )
    }
  }

  async removeProject(projectId) {
    const url = this.removeProjectUrlValue.replace('__PROJECT_ID__', projectId)
    try {
      const response = await fetch(url, {
        method: 'DELETE',
        credentials: 'same-origin',
      })
      if (response.ok) {
        const card = this.containerTarget.querySelector(
          `[data-project-id="${CSS.escape(projectId)}"]`,
        )
        if (card) {
          card.remove()
        }
        this.updateCount(-1)
      } else {
        showSnackbar(
          '#share-snackbar',
          this.element.dataset.transRemoveError || 'Failed to remove project.',
          SnackbarDuration.error,
        )
      }
    } catch (e) {
      console.error('Failed to remove project:', e)
      showSnackbar(
        '#share-snackbar',
        this.element.dataset.transRemoveError || 'Failed to remove project.',
        SnackbarDuration.error,
      )
    }
  }

  updateCount(delta = 0) {
    if (this.hasCountTarget) {
      const current = parseInt(this.countTarget.textContent) || 0
      this.countTarget.textContent = String(current + delta)
    }
  }
}
