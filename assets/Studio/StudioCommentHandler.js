import { showSnackbar } from '../Layout/Snackbar'

export default class {
  removeComment(studioID, element, commentID, isReply, parentID) {
    const removeError = document.getElementById('comment-remove-error').value

    fetch('../removeStudioComment/', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ studioID, commentID, parentID, isReply }),
    })
      .then((response) => {
        if (response.ok) {
          this.hideComment(element)
          this.updateCommentCount(-1)
          this.increaseActivityCount()
          if (isReply && parentID > 0) {
            const pc = document.getElementById('info-' + parentID)
            pc.textContent = (pc.textContent - 1).toString()
          }
        } else {
          console.error(response.status)
          showSnackbar('#share-snackbar', removeError)
        }
      })
      .catch((e) => {
        console.error(e)
        showSnackbar('#share-snackbar', removeError)
      })
  }

  // Function to post a comment
  postComment(studioID, isReply) {
    const comment = isReply
      ? document.querySelector('#add-reply input').value
      : document.querySelector('#add-comment textarea').value
    const commentError = document.getElementById('comment-error').value
    const parentID = isReply ? document.getElementById('cmtID').value : 0

    if (comment.trim() === '') {
      return
    }

    fetch('../postCommentToStudio/', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ studioID, comment, parentID }),
    })
      .then((response) => {
        if (response.ok) {
          if (isReply) {
            // ToDo: create template just for comment, and make it injectable via js but use twig
            // document.querySelector('#add-reply input').value = '';
            // document.getElementById('info-' + parentID).textContent = response.replies_count;
          } else {
            // ToDo: create template just for comment, and make it injectable via js but use twig
            window.location.reload()
            document.querySelector('#add-comment textarea').value = ''
            this.updateCommentCount(1)
          }
          this.increaseActivityCount()
        } else {
          console.error(response.status)
          showSnackbar('#share-snackbar', commentError)
        }
      })
      .catch((e) => {
        console.error(e)
        showSnackbar('#share-snackbar', commentError)
      })
  }

  // Function to load replies
  loadReplies() {
    showSnackbar('#share-snackbar', 'Replies not yet supported')
    // document.getElementById('modal-body').innerHTML = '';
    // document.getElementById('cmtID').value = commentID;
    //
    // fetch('../loadCommentReplies/', {
    //   method: 'GET',
    //   headers: {
    //     'Content-Type': 'application/json'
    //   },
    //   body: JSON.stringify({ commentID })
    // })
    //   .then(response => response.text())
    //   .then(data => {
    //     document.getElementById('comment-replies-body').innerHTML = data;
    //   })
    //   .catch(() => {
    //     document.getElementById('comment-replies-body').innerHTML = '<h1>Failed to load replies</h1>';
    //   });
  }

  showNoCommentsInfoMessage() {
    document.getElementById('no-comments').style.display = 'block'
  }

  hideNoCommentsInfoMessage() {
    document.getElementById('no-comments').style.display = 'none'
  }

  hideComment(element) {
    element.closest('.studio-comment').style.display = 'none'
    element.nextElementSibling.style.display = 'none'
  }

  updateCommentCount(byCount) {
    const cc = document.getElementById('comments-count')
    const newCount = parseInt(cc.textContent) + byCount
    cc.textContent = newCount.toString()
    if (newCount <= 0) {
      this.showNoCommentsInfoMessage()
    } else {
      this.hideNoCommentsInfoMessage()
    }
  }

  increaseActivityCount() {
    const tc = document.getElementById('activity_count')
    tc.textContent = (parseInt(tc.textContent) + 1).toString()
  }
}
