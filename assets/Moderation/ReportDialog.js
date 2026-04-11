import Swal from 'sweetalert2'
import { handleAccountState403 } from '../Security/AccountStateErrorHandler'
import { escapeAttr, escapeHtml } from '../Components/HtmlEscape'
import { REPORT_CATEGORIES } from './ReportCategories'

const SESSION_KEY_PREFIX = 'pendingReport_'
const REPORT_CATEGORY_ICONS = {
  copyright: 'copyright',
  sexual_content: 'no_adult_content',
  graphic_violence: 'warning',
  hateful_content: 'block',
  improper_rating: 'report_problem',
  spam: 'alternate_email',
  other: 'more_horiz',
  inappropriate: 'error_outline',
  harassment: 'front_hand',
  impersonation: 'badge',
  inappropriate_profile: 'person_off',
  spam_account: 'person_search',
  inappropriate_content: 'hide_source',
}

export function showReportDialog({
  contentType,
  contentId,
  apiUrl,
  loginUrl,
  isLoggedIn,
  translations,
}) {
  if (!isLoggedIn) {
    sessionStorage.setItem(
      'pendingAction',
      JSON.stringify({
        contentType,
        contentId,
        actionType: 'report',
      }),
    )
    window.location.href = loginUrl
    return
  }

  const categories = REPORT_CATEGORIES[contentType] || []
  const sessionKey = SESSION_KEY_PREFIX + contentType + '_' + contentId
  const oldData = JSON.parse(sessionStorage.getItem(sessionKey) || '{}')

  Swal.fire({
    title: translations.title || 'Report',
    html: buildReportHtml(categories, translations, oldData),
    focusConfirm: false,
    showCancelButton: true,
    allowOutsideClick: false,
    customClass: {
      confirmButton: 'btn btn-primary',
      cancelButton: 'btn btn-outline-primary',
    },
    buttonsStyling: false,
    confirmButtonText: translations.submit || 'Submit Report',
    cancelButtonText: translations.cancel || 'Cancel',
    preConfirm: () => {
      const popup = Swal.getPopup()
      const category = popup?.querySelector('#report-category-value')?.value || ''
      const note = popup?.querySelector('#report-note')?.value || ''

      if (!category) {
        Swal.showValidationMessage(translations.selectCategory || 'Please select a category')
        return false
      }

      return submitReport(apiUrl, { category, note }, translations, sessionKey)
    },
    didOpen: (popup) => {
      const categoryValueInput = popup.querySelector('#report-category-value')
      const categoryButtons = popup.querySelectorAll('[data-report-category]')

      const updateCategorySelection = (category) => {
        if (!categoryValueInput) {
          return
        }

        categoryValueInput.value = category
        categoryButtons.forEach((button) => {
          const isActive = button.dataset.reportCategory === category
          button.classList.toggle('is-active', isActive)
          button.setAttribute('aria-checked', isActive ? 'true' : 'false')

          const checkIcon = button.querySelector('.report-dialog__option__check')
          if (checkIcon) {
            checkIcon.textContent = isActive ? 'radio_button_checked' : 'radio_button_unchecked'
          }
        })
      }

      categoryButtons.forEach((button) => {
        button.addEventListener('click', () => {
          updateCategorySelection(button.dataset.reportCategory)
          sessionStorage.setItem(
            sessionKey,
            JSON.stringify({
              category: button.dataset.reportCategory,
              note: popup.querySelector('#report-note')?.value || '',
            }),
          )
        })
      })

      // Persist form state in session (scoped to popup to avoid listener leak)
      popup.addEventListener('input', (e) => {
        if (e.target.id === 'report-note') {
          sessionStorage.setItem(
            sessionKey,
            JSON.stringify({
              category: categoryValueInput?.value || '',
              note: popup.querySelector('#report-note')?.value || '',
            }),
          )
        }
      })
    },
  })
}

function submitReport(apiUrl, { category, note }, translations, sessionKey) {
  return fetch(apiUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ category, note: note || null }),
  })
    .then((response) => {
      if (response.status === 204) {
        sessionStorage.removeItem(sessionKey)
        Swal.fire({
          text: translations.success || 'Your report has been submitted.',
          icon: 'success',
          customClass: { confirmButton: 'btn btn-primary' },
          buttonsStyling: false,
          confirmButtonText: translations.confirm || 'Confirm',
        })
        return true
      } else if (response.status === 409) {
        Swal.showValidationMessage(
          translations.duplicate || "You've already reported this content.",
        )
        return false
      } else if (response.status === 429) {
        Swal.showValidationMessage(
          translations.rateLimited ||
            "You're submitting reports too quickly. Please wait and try again.",
        )
        return false
      } else if (response.status === 403) {
        return handleAccountState403(
          response,
          translations,
          translations.trustTooLow || 'Your account is too new to file reports.',
        ).then(() => false)
      } else {
        Swal.showValidationMessage(
          translations.error || 'Oops, that did not work. Please try again!',
        )
        return false
      }
    })
    .catch(() => {
      Swal.showValidationMessage(translations.error || 'Oops, that did not work. Please try again!')
      return false
    })
}

function buildReportHtml(categories, translations, oldData) {
  const selectedCategory = oldData.category || ''

  const categoryHtml = categories
    .map((cat) => {
      const isActive = selectedCategory === cat.value
      const label = getCategoryLabel(cat, translations)
      const icon = REPORT_CATEGORY_ICONS[cat.labelKey] || 'flag'
      return `
      <button type="button"
        class="report-dialog__option${isActive ? ' is-active' : ''}"
        data-report-category="${escapeAttr(cat.value)}"
        role="radio"
        aria-checked="${isActive ? 'true' : 'false'}">
        <span class="report-dialog__option__icon material-icons" aria-hidden="true">${icon}</span>
        <span class="report-dialog__option__label">${escapeHtml(label)}</span>
        <span class="report-dialog__option__check material-icons" aria-hidden="true">${isActive ? 'radio_button_checked' : 'radio_button_unchecked'}</span>
      </button>`
    })
    .join('')

  const placeholder =
    translations.notePlaceholder || 'Please describe why you are reporting this...'
  const noteValue = oldData.note || ''

  return `
    <div class="report-dialog">
      <div class="report-dialog__options" role="radiogroup" aria-label="${escapeAttr(translations.title || 'Report')}">
        ${categoryHtml}
      </div>
      <input id="report-category-value" type="hidden" value="${escapeAttr(selectedCategory)}">
      <label class="report-dialog__note-label" for="report-note">${escapeHtml(translations.noteLabel || 'Additional details')}</label>
      <label class="mdc-text-field mdc-text-field--outlined mdc-text-field--textarea report-reason report-dialog__note">
        <span class="mdc-text-field__resizer">
          <textarea class="mdc-text-field__input" id="report-note"
            placeholder="${escapeAttr(placeholder)}">${escapeHtml(noteValue)}</textarea>
        </span>
        <span class="mdc-notched-outline">
          <span class="mdc-notched-outline__leading"></span>
          <span class="mdc-notched-outline__trailing"></span>
        </span>
      </label>
    </div>
  `
}

function getCategoryLabel(category, translations) {
  const translatedLabel = translations['category_' + category.labelKey]

  if (translatedLabel) {
    return translatedLabel
  }

  return category.labelKey.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}
