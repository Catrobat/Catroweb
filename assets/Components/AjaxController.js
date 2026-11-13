import { Controller } from '@hotwired/stimulus'
import AcceptLanguage from '../Api/AcceptLanguage'

export class AjaxController extends Controller {
  /**
   * Wrapper for POST Requests in our API
   *
   * @param {string} url
   * @param {object} data
   * @returns {Promise<Response>}
   */
  fetchPost(url, data) {
    return fetch(url, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'Accept-Language': new AcceptLanguage().get(),
      },
      body: JSON.stringify(data),
    })
  }
}
