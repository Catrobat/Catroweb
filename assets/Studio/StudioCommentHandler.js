import { showSnackbar, SnackbarDuration } from '../Layout/Snackbar'

export default class {
  removeComment(studioID, element, commentID, isReply, parentID) {
    const removeError = document.getElementById('comment-remove-error').value

    fetch('/removeStudioComment/', {
      method: 'DELETE',
      credentials: 'same-origin',
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
          showSnackbar('#share-snackbar', removeError, SnackbarDuration.error)
        }
      })
      .catch((e) => {
        console.error(e)
        showSnackbar('#share-snackbar', removeError, SnackbarDuration.error)
      })
  }

  postComment(studioID, isReply) {
    const comment = isReply
      ? document.querySelector('#add-reply input').value
      : document.querySelector('#add-comment textarea').value
    const commentError = document.getElementById('comment-error').value
    const parentID = isReply ? document.getElementById('cmtID').value : 0

    if (comment.trim() === '') {
      return
    }

    fetch('/postCommentToStudio/', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ studioID, comment, parentID }),
    })
      .then((response) => {
        if (response.ok) {
          if (isReply) {
            window.location.reload()
          } else {
            window.location.reload()
          }
        } else if (response.status === 429) {
          const rateLimitedMsg =
            document.getElementById('comment-rate-limited')?.value ||
            "You're posting comments too quickly. Please wait a moment."
          showSnackbar('#share-snackbar', rateLimitedMsg, SnackbarDuration.error)
        } else {
          console.error(response.status)
          showSnackbar('#share-snackbar', commentError, SnackbarDuration.error)
        }
      })
      .catch((e) => {
        console.error(e)
        showSnackbar('#share-snackbar', commentError, SnackbarDuration.error)
      })
  }

  loadReplies(studioID, element, commentID) {
    const commentError = document.getElementById('comment-error')?.value || 'Failed to load replies'

    document.getElementById('modal-body').innerHTML = ''
    document.getElementById('cmtID').value = commentID

    fetch(`/loadCommentReplies/?commentID=${encodeURIComponent(commentID)}`, {
      method: 'GET',
      credentials: 'same-origin',
    })
      .then((response) => {
        if (response.ok) {
          return response.json()
        }
        throw new Error('Failed to load replies')
      })
      .then((html) => {
        document.getElementById('modal-body').innerHTML = html
      })
      .catch((e) => {
        console.error(e)
        const modalBody = document.getElementById('modal-body')
        modalBody.innerHTML = ''
        const p = document.createElement('p')
        p.className = 'text-center text-muted mt-3'
        p.textContent = commentError
        modalBody.appendChild(p)
      })
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
