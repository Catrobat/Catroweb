export class CustomTranslationApi {
  /**
   * @param {Promise<string[]|null>|null} definedLanguages the project's custom-translation languages, fetched once
   *   per page; lookups for other languages are skipped instead of each answering 404.
   */
  constructor(programSection, baseUrl = '', definedLanguages = null) {
    this.programSection = programSection === 'credits' ? 'credit' : programSection
    this.baseUrl = baseUrl
    this.definedLanguages = definedLanguages
  }

  async getCustomTranslation(programId, language, successCallback, errorCallback = () => {}) {
    const languages = await this.definedLanguages
    if (Array.isArray(languages) && !languages.includes(language)) {
      errorCallback(new Error('No custom translation for this language'))
      return
    }

    try {
      const response = await fetch(
        `${this.baseUrl}/api/projects/${programId}/translation/${this.programSection}/${language}`,
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
        `${this.baseUrl}/api/projects/${programId}/translation/${this.programSection}/${language}`,
        {
          method: 'DELETE',
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
        `${this.baseUrl}/api/projects/${programId}/translation/${this.programSection}/${language}`,
        {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
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
      const response = await fetch(
        `${this.baseUrl}/api/projects/${programId}/translation/languages`,
        {
          method: 'GET',
        },
      )

      if (!response.ok) {
        throw new Error(`Error: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      throw new Error(`Error fetching languages: ${error.message}`, { cause: error })
    }
  }
}
