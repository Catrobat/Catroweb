export class CustomTranslationApi {
  constructor(programSection) {
    this.programSection =
      programSection === 'credits' ? 'credit' : programSection
  }

  async getCustomTranslation(
    programId,
    language,
    successCallback,
    errorCallback = () => {},
  ) {
    try {
      const response = await fetch(
        `../translate/custom/project/${programId}?field=${this.programSection}&language=${language}`,
        {
          method: 'GET',
        },
      )

      if (!response.ok) {
        throw new Error(`Error: ${response.status}`)
      }
      const data = await response.text()
      successCallback(data)
    } catch (error) {
      errorCallback(error)
    }
  }

  deleteCustomTranslation(programId, language, successCallback, errorCallback) {
    const self = this
    return new Promise((resolve, reject) => {
      fetch(
        `../translate/custom/project/${programId}?field=${self.programSection}&language=${language}`,
        {
          method: 'DELETE',
        },
      )
        .then((response) => {
          if (!response.ok) {
            throw new Error(`Error: ${response.status}`)
          }
          successCallback(language)
          resolve()
        })
        .catch((error) => {
          errorCallback(error)
          reject(error)
        })
    })
  }

  saveCustomTranslation(
    programId,
    text,
    language,
    successCallback,
    errorCallback,
  ) {
    const self = this
    return new Promise((resolve, reject) => {
      fetch(
        `../translate/custom/project/${programId}?field=${self.programSection}&text=${text}&language=${language}`,
        {
          method: 'PUT',
        },
      )
        .then((response) => {
          if (!response.ok) {
            throw new Error(`Error: ${response.status}`)
          }
          successCallback(language)
          resolve()
        })
        .catch((error) => {
          errorCallback(error)
          reject(error)
        })
    })
  }

  async getCustomTranslationLanguages(programId) {
    try {
      const response = await fetch(
        `../translate/custom/project/${programId}/list`,
        {
          method: 'GET',
        },
      )

      if (!response.ok) {
        throw new Error(`Error: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      throw new Error(`Error fetching languages: ${error.message}`)
    }
  }
}
