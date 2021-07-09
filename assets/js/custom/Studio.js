/* eslint-env jquery */
// eslint-disable-next-line no-unused-vars
const Studio = function () {
  this.showMoreLessDescription = function (element) {
    const more = $('#showMore-text').val()
    const less = $('#showLess-text').val()
    $('#studio-desc').toggleClass('desc-show-less')
    if (element.text() === more) {
      element.text(less)
    } else {
      element.text(more)
    }
  }

  this.removeProject = function (projectID) {
    const removeSuccess = $('#project-remove-success').val()
    const removeError = $('#project-remove-error').val()
    const studioID = $('#std-id').val()
    $.ajax({
      url: '../removeStudioProject/',
      type: 'POST',
      data: {
        studioID: studioID,
        projectID: projectID
      },
      success: function (data, status) {
        if (status === 'success') {
          // eslint-disable-next-line no-undef
          showSnackbar('#share-snackbar', removeSuccess)
          $('#project-' + projectID).fadeOut()
          $('#projects-count').text(data.projects_count)
          $('#activities_count').text(data.activities_count)
        } else {
          // eslint-disable-next-line no-undef
          showSnackbar('#share-snackbar', removeError)
        }
      },
      fail: function () {
        // eslint-disable-next-line no-undef
        showSnackbar('#share-snackbar', removeError)
      }
    })
  }

  this.removeComment = function (element, commentID, isReply, parentID) {
    const studioID = $('#std-id').val()
    const removeError = $('#comment-remove-error').val()
    $.ajax({
      url: '../removeStudioComment/',
      type: 'POST',
      data: {
        studioID: studioID,
        commentID: commentID,
        parentID: parentID,
        isReply: isReply
      },
      success: function (data, status) {
        if (status === 'success') {
          element.parents('.studio-comment').fadeOut().next('hr').fadeOut()
          $('#comments-count').text(data.comments_count)
          $('#activities_count').text(data.activities_count)
          if (isReply && parentID > 0) {
            $('#info-' + parentID).text(data.replies_count)
          }
        } else {
          // eslint-disable-next-line no-undef
          showSnackbar('#share-snackbar', removeError)
        }
      },
      fail: function () {
        // eslint-disable-next-line no-undef
        showSnackbar('#share-snackbar', removeError)
      }
    })
  }

  this.postComment = function (isReply) {
    const studioID = $('#std-id').val()
    const comment = isReply ? $('#add-reply').find('input').val() : $('#add-comment').find('input').val()
    const commentError = $('#comment-error').val()
    const parentID = isReply ? $('#cmtID').val() : 0
    if (comment.trim() === '') {
      return
    }
    $.ajax({
      url: '../postCommentToStudio/',
      type: 'POST',
      data: {
        studioID: studioID,
        comment: comment,
        isReply: isReply,
        parentID: parentID
      },
      success: function (data, status) {
        if (status === 'success') {
          if (isReply) {
            $('#add-reply').before(data.comment).find('input').val('')
            $('#info-' + parentID).text(data.replies_count)
          } else {
            $('#add-comment').before(data.comment).find('input').val('')
            $('#comments-count').text(data.comments_count)
            $('#no-comments').hide()
          }
          $('#activities_count').text(data.activities_count)
        } else {
          // eslint-disable-next-line no-undef
          showSnackbar('#share-snackbar', commentError)
        }
      },
      fail: function () {
        // eslint-disable-next-line no-undef
        showSnackbar('#share-snackbar', commentError)
      }
    })
  }

  this.loadReplies = function (commentID) {
    $('#modal-body').html('')
    $('#cmtID').val(commentID)
    $.ajax({
      url: '../loadCommentReplies/',
      type: 'GET',
      data: {
        commentID: commentID
      },
      success: function (data, status) {
        if (status === 'success') {
          $('#comment-replies-body').html(data)
        }
      },
      fail: function () {
        $('#comment-replies-body').html('<h1>Failed to load replies</h1>')
      }
    })
  }

  this.updateHeader = function () {
    $('#std-header').val('').trigger('click')
  }

  this.uploadHeader = function () {
    const updateCoverError = $('#update-cover-error').val()
    if ($('#std-header').val() !== '') {
      $.ajax({
        type: 'POST',
        url: '../uploadStudioCover/',
        cache: false,
        processData: false,
        contentType: false,
        data: new FormData(document.getElementById('std-header-form')),
        success: function (data, status) {
          if (status === 'success') {
            $('#studio-img-container').find('img').attr('src', data.new_cover)
          } else {
            // eslint-disable-next-line no-undef
            showSnackbar('#share-snackbar', updateCoverError)
          }
        },
        fail: function () {
          // eslint-disable-next-line no-undef
          showSnackbar('#share-snackbar', updateCoverError)
        }
      })
    }
  }
}
