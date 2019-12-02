function setImageUploadListener(upload_url, upload_button_container_id, image_container_id,
                                statusCode_OK, statusCode_UPLOAD_EXCEEDING_FILESIZE,
                                statusCode_UPLOAD_UNSUPPORTED_MIME_TYPE)
{
  $(upload_button_container_id).find('input[type=file]').change(function (data) {

    $('.error-message').addClass('d-none')

    let file = data.target.files[0]

    let image_upload = $(upload_button_container_id)
    image_upload.find('span').hide()
    image_upload.find('.button-show-ajax').show()

    let reader = new FileReader()

    reader.onerror = function () {
      $('.text-img-upload-error').removeClass('d-none')
    }

    reader.onload = function (event) {
      $.post(upload_url, {image: event.currentTarget.result}, function (data) {
        switch (parseInt(data.statusCode))
        {
          case statusCode_OK:
            $('.text-img-upload-success').removeClass('d-none')
            if (data.image_base64 === null) {
              let src = $(image_container_id).attr('src')
              let d = new Date()
              $(image_container_id).attr('src', src + '?a=' + d.getDate())
            }
            else {
              $(image_container_id).attr('src', data.image_base64)
            }
            break

          case statusCode_UPLOAD_EXCEEDING_FILESIZE:
            $('.text-img-upload-too-large').removeClass('d-none')
            break

          case statusCode_UPLOAD_UNSUPPORTED_MIME_TYPE:
            $('.text-mime-type-not-supported').removeClass('d-none')
            break

          default:
            $('.text-img-upload-error').removeClass('d-none')
        }

        let image_upload = $(upload_button_container_id)
        image_upload.find('span').show()
        image_upload.find('.button-show-ajax').hide()
      })
    }
    reader.readAsDataURL(file)
  })
}
