/* global globalConfiguration */

import { getCookie } from '../Security/CookieHelper'
import MessageDialogs from '../Components/MessageDialogs'

export class ApiFetch {
  constructor(url, method = 'GET', data = undefined, expect = 'none') {
    this.url = url
    this.method = method
    this.data = data
    this.expect = expect
  }

  generateAuthenticatedFetch() {
    const config = {
      method: this.method,
      headers: {
        Authorization: 'Bearer ' + getCookie('BEARER'),
      },
    }

    if (this.data !== undefined) {
      config.body = JSON.stringify(this.data)
      config.headers['Content-type'] = 'application/json'
    }

    return window.fetch(this.url, config)
  }

  async run() {
    const response = await this.generateAuthenticatedFetch()

    let data
    switch (this.expect) {
      case 'json':
        data = await response.json()
        break
      case 'text':
        data = await response.text()
        break
      case 'blob':
        data = await response.blob()
        break
      case 'arrayBuffer':
        data = await response.arrayBuffer()
        break
    }

    if (response.ok) {
      return Promise.resolve(data)
    } else {
      const errorMessage =
        'ERROR ' + response.status + ': ' + JSON.stringify(data)
      return Promise.reject(new Error(errorMessage))
    }
  }
}

export class ApiPutFetch {
  constructor(
    url,
    data,
    componentName,
    unspecifiedErrorText,
    successCallback,
    otherErrorMessages = undefined,
    finalCallback = undefined,
  ) {
    this.url = url
    this.data = data
    this.componentName = componentName
    if (
      typeof unspecifiedErrorText === 'string' &&
      unspecifiedErrorText.length > 0
    ) {
      this.unspecifiedErrorText = unspecifiedErrorText
    } else {
      this.unspecifiedErrorText =
        globalConfiguration.messages.unspecifiedErrorText
    }
    this.successCallback = successCallback
    this.otherErrorMessages = otherErrorMessages
    this.finalCallback = finalCallback
  }

  run() {
    new ApiFetch(this.url, 'PUT', this.data)
      .generateAuthenticatedFetch()
      .then((response) => {
        switch (response.status) {
          case 204:
            if (typeof this.successCallback === 'function') {
              this.successCallback()
            }
            break
          case 401:
            // Invalid credentials
            console.error(
              this.componentName + ' ERROR 401: Invalid credentials',
              response,
            )
            MessageDialogs.showErrorMessage(
              globalConfiguration.messages.authenticationErrorText,
            )
            break
          case 422:
            response.json().then((errors) => {
              console.error(this.componentName + ' ERROR 422', errors, response)
              MessageDialogs.showErrorList(errors)
            })
            break
          default:
            console.error(
              this.componentName + ' ERROR ' + response.status,
              response,
            )
            if (
              Object.prototype.hasOwnProperty.call(
                this.otherErrorMessages,
                response.status,
              )
            ) {
              const errorHandler = this.otherErrorMessages[response.status]
              if (typeof errorHandler === 'function') {
                errorHandler(response)
              } else if (typeof errorHandler === 'string') {
                MessageDialogs.showErrorMessage(errorHandler)
              } else {
                MessageDialogs.showErrorMessage(this.unspecifiedErrorText)
              }
            } else {
              MessageDialogs.showErrorMessage(this.unspecifiedErrorText)
            }
            break
        }
        if (typeof this.finalCallback === 'function') this.finalCallback()
      })
      .catch((reason) => {
        console.error(this.componentName + ' FAILURE', reason)
        MessageDialogs.showErrorMessage(this.unspecifiedErrorText)
        if (typeof this.finalCallback === 'function') this.finalCallback()
      })
  }
}

export class ApiDeleteFetch {
  constructor(
    url,
    componentName,
    unspecifiedErrorText,
    successCallback,
    otherErrorMessages = undefined,
    finalCallback = undefined,
  ) {
    this.url = url
    this.componentName = componentName
    if (
      typeof unspecifiedErrorText === 'string' &&
      unspecifiedErrorText.length > 0
    ) {
      this.unspecifiedErrorText = unspecifiedErrorText
    } else {
      this.unspecifiedErrorText =
        globalConfiguration.messages.unspecifiedErrorText
    }
    this.successCallback = successCallback
    this.otherErrorMessages = otherErrorMessages
    this.finalCallback = finalCallback
  }

  run() {
    new ApiFetch(this.url, 'DELETE')
      .generateAuthenticatedFetch()
      .then((response) => {
        switch (response.status) {
          case 204:
            if (typeof this.successCallback === 'function') {
              this.successCallback()
            }
            break
          case 401:
            // Invalid credentials
            console.error(
              this.componentName + ' ERROR 401: Invalid credentials',
              response,
            )
            MessageDialogs.showErrorMessage(
              globalConfiguration.messages.authenticationErrorText,
            )
            break
          default:
            console.error(
              this.componentName + ' ERROR ' + response.status,
              response,
            )
            if (
              Object.prototype.hasOwnProperty.call(
                this.otherErrorMessages,
                response.status,
              )
            ) {
              const errorHandler = this.otherErrorMessages[response.status]
              if (typeof errorHandler === 'function') {
                errorHandler(response)
              } else if (typeof errorHandler === 'string') {
                MessageDialogs.showErrorMessage(errorHandler)
              } else {
                MessageDialogs.showErrorMessage(this.unspecifiedErrorText)
              }
            } else {
              MessageDialogs.showErrorMessage(this.unspecifiedErrorText)
            }
            break
        }
        if (typeof this.finalCallback === 'function') this.finalCallback()
      })
      .catch((reason) => {
        console.error(this.componentName + ' FAILURE', reason)
        MessageDialogs.showErrorMessage(this.unspecifiedErrorText)
        if (typeof this.finalCallback === 'function') this.finalCallback()
      })
  }
}
