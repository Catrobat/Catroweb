import { Controller } from '@hotwired/stimulus'

export class AjaxController extends Controller {
  /**
   * Fetching some HTML data from an url then injecting it into a container
   *
   * @param {string} url
   * @param {string} elementId
   * @param {URLSearchParams} params
   * @returns {Promise<void>}
   */
  async fetchData (url, elementId, params) {
    const listElement = document.getElementById(elementId)
    listElement.innerHTML = ''

    // eslint-disable-next-line no-undef
    const response = await fetch(url + '?' + params.toString())
    listElement.innerHTML = await response.text()
  }

  /**
   * Wrapper for fetch with method put
   *
   * @param {string} url
   * @param {object} data
   * @returns {Promise<Response>}
   */
  fetchPut (url, data) {
    // eslint-disable-next-line no-undef
    return fetch(url, {
      method: 'PUT',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    })
  }
}
