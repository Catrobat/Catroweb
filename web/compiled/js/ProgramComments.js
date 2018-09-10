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
    amountOfVisibleComments = JSON.parse(window.sessionStorage.getItem('visibleComments'))
    if (amountOfVisibleComments == null || amountOfVisibleComments < minAmountOfVisibleComments)
    {
      amountOfVisibleComments = minAmountOfVisibleComments
    }
    else if (amountOfVisibleComments > totalAmountOfComments)
    {
      amountOfVisibleComments = totalAmountOfComments
    }
    console.log('V: ' + amountOfVisibleComments)
    
    $('.single-comment').each(function (index, el) {
      if (index < amountOfVisibleComments)
      {
        $(el).show()
      }
    })
    
    updateButtonVisibility()
    
    // increase icon size while hovering over it
    $('.icon-button').hover(
      function () {
        $(this).addClass('fa-lg')
      },
      function () {
        $(this).removeClass('fa-lg')
      }
    )
  })
  
  $(document).on('click', '#comment-post-button', function () {
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
          updateButtonVisibility()
        }
      },
      error  : function () {
        swal(defaultErrorMessage)
      }
    })
  })
  
  $(document).on('click', '.comment-delete-button', function () {
    let commentId = $(this).attr('id').substring('comment-delete-button-'.length)
    askForConfirmation(deleteComment, commentId, deleteConfirmation, deleteIt)
  })
  
  $(document).on('click', '.comment-report-button', function () {
    let commentId = $(this).attr('id').substring('comment-delete-button-'.length)
    askForConfirmation(reportComment, commentId, reportConfirmation, reportIt)
  })
  
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
          showSuccessPopUp(popUpDeletedTitle, popUpDeletedText)
          $('#comment-' + commentId).remove()
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
    )
  }
  
  function redirectToLogin ()
  {
    window.location.href = '../login'
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
  
  $(document).on('click', '#show-more-comments-button', function () {
    amountOfVisibleComments = Math.min(amountOfVisibleComments + showStep, totalAmountOfComments)
    window.sessionStorage.setItem('visibleComments', JSON.stringify(amountOfVisibleComments))
    
    $('.single-comment').each(function (index, el) {
      if (index >= amountOfVisibleComments - showStep &&
        index < amountOfVisibleComments && index < totalAmountOfComments)
      {
        $(el).show()
      }
    })
    
    if (amountOfVisibleComments > minAmountOfVisibleComments)
    {
      $('#show-less-comments-button').show()
    }
    if (amountOfVisibleComments >= totalAmountOfComments)
    {
      $(this).hide()
    }
  })
  
  $(document).on('click', '#show-less-comments-button', function () {
    console.log('show less')
    amountOfVisibleComments = Math.max(amountOfVisibleComments - showStep, minAmountOfVisibleComments)
    window.sessionStorage.setItem('visibleComments', JSON.stringify(amountOfVisibleComments))
    
    $('.single-comment').each(function (index, el) {
      if (index < amountOfVisibleComments + showStep && index >= amountOfVisibleComments &&
        index >= minAmountOfVisibleComments)
      {
        $(el).hide()
      }
    })
    
    if (amountOfVisibleComments <= minAmountOfVisibleComments)
    {
      $(this).hide()
    }
    if (amountOfVisibleComments < totalAmountOfComments)
    {
      $('#show-more-comments-button').show()
    }
  })
  
}

