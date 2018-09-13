/*
  Generated File by Grunt
  Sourcepath: web/js
*/
function ProgramComments (programId, visibleComments, showStep, minAmountOfVisibleComments,
                          totalAmountOfComments, cancel, deleteIt, reportIt, areYouSure,
                          noWayOfReturn, deleteConfirmation, reportConfirmation,
                          popUpCommentReportedTitle, popUpCommentReportedText,
                          popUpDeletedTitle, popUpDeletedText,
                          noAdminRightsMessage, defaultErrorMessage,
                          statusCode_OK, statusCode_NOT_LOGGED_IN, statusCode_NO_ADMIN_RIGHTS)
{
  let amountOfVisibleComments
  
  $(function () {
    amountOfVisibleComments = visibleComments
    restoreAmountOfVisibleCommentsFromSession()
    updateCommentsVisibility()
    updateButtonVisibility()
  })
  
  $(document).on('click', '#comment-post-button', function () {
    postComment()
  })
  
  $(document).on('click', '.comment-delete-button', function () {
    let commentId = $(this).attr('id').substring('comment-delete-button-'.length)
    askForConfirmation(deleteComment, commentId, deleteConfirmation, deleteIt)
  })
  
  $(document).on('click', '.comment-report-button', function () {
    let commentId = $(this).attr('id').substring('comment-delete-button-'.length)
    askForConfirmation(reportComment, commentId, reportConfirmation, reportIt)
  })
  
  $(document).on('click', '.add-comment-button', function () {
    let commentWrapper = $('#user-comment-wrapper')
    let showCommentWrapperButton = $('#show-add-comment-button')
    let hideCommentWrapperButton = $('#hide-add-comment-button')
    if (commentWrapper.is(':visible'))
    {
      commentWrapper.slideUp()
      hideCommentWrapperButton.hide()
      showCommentWrapperButton.show()
    }
    else
    {
      commentWrapper.slideDown()
      showCommentWrapperButton.hide()
      hideCommentWrapperButton.show()
    }
  })
  
  $(document).on('click', '#show-more-comments-button', function () {
    showMore(showStep)
  })
  
  $(document).on('click', '#show-less-comments-button', function () {
    showLess(showStep)
  })
  
  function postComment ()
  {
    let msg = $('#comment-message').val()
    if (msg.length === 0)
    {
      return
    }
    $.ajax({
      url    : '../comment',
      type   : 'post',
      data   : {Message: msg, ProgramId: programId},
      success: function (data) {
        if (data === statusCode_NOT_LOGGED_IN)
        {
          redirectToLogin()
        }
        else
        {
          $('#comments-wrapper').load(' #comments-wrapper')
          $('#comment-message').val('')
          location.reload()
        }
      },
      error  : function () {
        swal(defaultErrorMessage)
      }
    })
  }
  
  function deleteComment (commentId)
  {
    $.ajax({
      url    : '../deleteComment',
      type   : 'get',
      data   : {ProgramId: programId, CommentId: commentId},
      success: function (data) {
        if (data === statusCode_NOT_LOGGED_IN)
        {
          redirectToLogin()
        }
        else if (data === statusCode_NO_ADMIN_RIGHTS)
        {
          swal(noAdminRightsMessage)
        }
        else
        {
          $('#comment-' + commentId).remove()
          showSuccessPopUp(popUpDeletedTitle, popUpDeletedText)
        }
      },
      error  : function () {
        swal(defaultErrorMessage)
      }
    })
  }
  
  function reportComment (commentId)
  {
    $.ajax({
      url    : '../reportComment',
      type   : 'get',
      data   : {ProgramId: programId, CommentId: commentId},
      success: function (data) {
        if (data === statusCode_NOT_LOGGED_IN)
        {
          redirectToLogin()
        }
        else
        {
          showSuccessPopUp(popUpCommentReportedTitle, popUpCommentReportedText)
        }
      },
      error  : function () {
        swal(defaultErrorMessage)
      }
    })
  }
  
  function askForConfirmation (continueWithAction, commentId, text, okayText)
  {
    swal({
      title             : areYouSure,
      html              : text + '<br><br>' + noWayOfReturn,
      type              : 'warning',
      showCancelButton  : true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor : '#d33',
      confirmButtonText : okayText,
      cancelButtonText  : cancel
    }).then(() => {
      continueWithAction(commentId)
    })
  }
  
  function showSuccessPopUp (title, text)
  {
    swal(
      {
        title             : title,
        text              : text,
        type              : 'success',
        confirmButtonClass: 'btn btn-success',
      }
    ).then(() => {
      location.reload()
    })
  }
  
  function redirectToLogin ()
  {
    window.location.href = '../login'
  }
  
  function restoreAmountOfVisibleCommentsFromSession ()
  {
    let lastSessionAmount = JSON.parse(window.sessionStorage.getItem('visibleComments'))
    if (lastSessionAmount !== null)
    {
      amountOfVisibleComments = lastSessionAmount
    }
    if (amountOfVisibleComments > totalAmountOfComments)
    {
      amountOfVisibleComments = totalAmountOfComments
    }
  }
  
  function updateCommentsVisibility ()
  {
    $('.single-comment').each(function (index, comment) {
      if (index < amountOfVisibleComments)
      {
        $(comment).show()
      }
      else
      {
        $(comment).hide()
      }
    })
  }
  
  function updateButtonVisibility ()
  {
    if (amountOfVisibleComments > minAmountOfVisibleComments)
    {
      $('#show-less-comments-button').show()
    }
    else
    {
      $('#show-less-comments-button').hide()
    }
    
    if (amountOfVisibleComments < totalAmountOfComments)
    {
      $('#show-more-comments-button').show()
    }
    else
    {
      $('#show-more-comments-button').hide()
    }
  }
  
  function showMore (step)
  {
    amountOfVisibleComments = Math.min(amountOfVisibleComments + step, totalAmountOfComments)
    window.sessionStorage.setItem('visibleComments', JSON.stringify(amountOfVisibleComments))
    updateCommentsVisibility()
    updateButtonVisibility()
  }
  
  function showLess (step)
  {
    amountOfVisibleComments = Math.max(amountOfVisibleComments - step, minAmountOfVisibleComments)
    window.sessionStorage.setItem('visibleComments', JSON.stringify(amountOfVisibleComments))
    updateCommentsVisibility()
    updateButtonVisibility()
  }
}

