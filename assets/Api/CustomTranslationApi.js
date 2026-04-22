import { getCookie } from '../Security/CookieHelper'

export class CustomTranslationApi {
  constructor(programSection) {
    this.programSection = programSection === 'credits' ? 'credit' : programSection
  }

  async getCustomTranslation(programId, language, successCallback, errorCallback = () => {}) {
    try {
      const response = await fetch(
        `/api/projects/${programId}/translation/${this.programSection}/${language}`,
        {
          method: 'GET',
        },
      )

      if (!response.ok) {
        throw new Error(`Error: ${response.status}`)
      }
      const data = await response.json()
      successCallback(data.translation)
    } catch (error) {
      errorCallback(error)
    }
  }

  async deleteCustomTranslation(programId, language, successCallback, errorCallback) {
    try {
      const response = await fetch(
        `/api/projects/${programId}/translation/${this.programSection}/${language}`,
        {
          method: 'DELETE',
          headers: {
            Authorization: 'Bearer ' + getCookie('BEARER'),
          },
        },
      )

      if (!response.ok) {
        throw new Error(`Error: ${response.status}`)
      }
      successCallback(language)
    } catch (error) {
      errorCallback(error)
    }
  }

  async saveCustomTranslation(programId, text, language, successCallback, errorCallback) {
    try {
      const response = await fetch(
        `/api/projects/${programId}/translation/${this.programSection}/${language}`,
        {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            Authorization: 'Bearer ' + getCookie('BEARER'),
          },
          body: JSON.stringify({ text }),
        },
      )

      if (!response.ok) {
        throw new Error(`Error: ${response.status}`)
      }
      successCallback(language)
    } catch (error) {
      errorCallback(error)
    }
  }

  async getCustomTranslationLanguages(programId) {
    try {
      const response = await fetch(`/api/projects/${programId}/translation/languages`, {
        method: 'GET',
      })

      if (!response.ok) {
        throw new Error(`Error: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      throw new Error(`Error fetching languages: ${error.message}`, { cause: error })
    }
  }
}
