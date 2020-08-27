/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function setImageUploadListener (uploadUrl, uploadButtonContainerId, imageContainerId,
  statusCodeOK, statusCodeUploadExceedingFilesize,
  statusCodeUploadUnsupportedMimeType) {
  $(uploadButtonContainerId).find('input[type=file]').change(function (data) {
    $('.error-message').addClass('d-none')

    const file = data.target.files[0]

    const imageUpload = $(uploadButtonContainerId)
    const imageUploadSpinner = $('#upload-image-spinner')
    const imageUploadSuccess = $('.text-img-upload-success')
    imageUpload.find('span').hide()
    imageUpload.find('.button-show-ajax').show()
    imageUploadSpinner.removeClass('d-none')

    const reader = new FileReader()

    reader.onerror = function () {
      $('.text-img-upload-error').removeClass('d-none')
      imageUploadSpinner.addClass('d-none')
    }
    reader.onload = function (event) {
      $.post(uploadUrl, { image: event.currentTarget.result }, function (data) {
        if (data.statusCode) {
          // Legacy fake statusCode
          switch (parseInt(data.statusCode)) {
            case statusCodeOK:
              imageUploadSuccess.removeClass('d-none')
              if (data.image_base64 === null) {
                const src = $(imageContainerId).attr('src')
                const d = new Date()
                $(imageContainerId).attr('src', src + '?a=' + d.getDate())
              } else {
                $(imageContainerId).attr('src', data.image_base64)
              }
              break
            case statusCodeUploadExceedingFilesize:
              $('.text-img-upload-too-large').removeClass('d-none')
              break
            case statusCodeUploadUnsupportedMimeType:
              $('.text-mime-type-not-supported').removeClass('d-none')
              break
            default:
              $('.text-img-upload-error').removeClass('d-none')
          }
        } else {
          imageUploadSuccess.removeClass('d-none')
          const src = $(imageContainerId).attr('src')
          const d = new Date()
          $(imageContainerId).attr('src', src + '?a=' + d.getDate())
        }
        const imageUpload = $(uploadButtonContainerId)
        imageUpload.find('span').show()
        imageUpload.find('.button-show-ajax').hide()
        imageUploadSpinner.addClass('d-none')
        setTimeout(function () {
          imageUploadSuccess.addClass('d-none')
        }, 3000)
      })
    }
    reader.readAsDataURL(file)
  })
}
