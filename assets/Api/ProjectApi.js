/* global globalConfiguration */
/* global projectConfiguration */

import MessageDialogs from '../Components/MessageDialogs'
import { ApiPatchFetch } from './ApiHelper'

export default class ProjectApi {
  constructor() {
    this.baseUrl = document.querySelector('#js-api-routing').dataset.baseUrl
  }

  updateProject(id, data, successCallback, finalCallback) {
    const msg403 =
      typeof projectConfiguration !== 'undefined'
        ? projectConfiguration.messages.forbidden
        : 'Forbidden'
    const msg404 =
      typeof projectConfiguration !== 'undefined'
        ? projectConfiguration.messages.notFound
        : 'Not found'
    new ApiPatchFetch(
      `${this.baseUrl}/api/projects/${id}`,
      data,
      'Save Project',
      null,
      successCallback,
      {
        403: msg403,
        404: msg404,
        500: (response) => {
          response
            .json()
            .then((data) => {
              MessageDialogs.showErrorMessage(data?.error?.message || data.error)
            })
            .catch(() => {
              const fallback =
                typeof globalConfiguration !== 'undefined'
                  ? globalConfiguration.messages.unspecifiedErrorText
                  : 'An error occurred'
              MessageDialogs.showErrorMessage(fallback)
            })
        },
      },
      finalCallback,
    ).run()
  }
}
