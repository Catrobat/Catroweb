import Swal from 'sweetalert2'
import './components/tab_bar'
import '../styles/custom/profile.scss'
import { showSnackbar } from './components/snackbar'

class FollowerOverview {
  constructor() {
    this.followerContainer = document.querySelector('.js-follower-overview')
    this.numberOfFollowings = this.followerContainer.dataset.numberOfFollowing
    this.emptyContainerMessage = document.querySelector('#no-followers')
  }

  registerEventListeners() {
    document.addEventListener('DOMContentLoaded', () => {
      this.registerFollowOnButtonClickEventListeners()
      this.registerUnfollowOnButtonClickEventListeners()
    })
  }

  unfollow(button, id, username) {
    button.disabled = true

    Swal.fire({
      title: this.followerContainer.dataset.unfollowQuestion,
      icon: 'question',
      showCancelButton: true,
      allowOutsideClick: false,
      confirmButtonText: this.followerContainer.dataset.unfollowButton.replace(
        '%username%',
        username,
      ),
      cancelButtonText: this.followerContainer.dataset.cancelButton,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
    }).then((result) => {
      if (result.value) {
        fetch(this.followerContainer.dataset.unfollowUrl + '/' + id, {
          method: 'delete',
        })
          .then((response) => {
            if (response.ok) {
              button.disabled = false
              --this.numberOfFollowings
              if (this.numberOfFollowings <= 0) {
                this.showEmptyContainerMessage()
              }
              window.location.reload()
            } else if (response.status === 401) {
              window.location.href = this.followerContainer.dataset.loginUrl
              return false
            } else {
              throw new Error('Unexpected error response: ' + response.status)
            }
          })
          .catch((error) => {
            this.showUnexpectedErrorSnackbar(error)
          })
          .finally(() => {
            button.disabled = false
          })
      } else {
        button.disabled = false
      }
    })
  }

  follow(button, id) {
    button.disabled = true

    fetch(this.followerContainer.dataset.followUrl + '/' + id, {
      method: 'post',
    })
      .then((response) => {
        if (response.ok) {
          ++this.numberOfFollowings
          this.hideEmptyContainerMessage()
          window.location.reload()
        } else if (response.status === 401) {
          window.location.href = this.followerContainer.dataset.loginUrl
          return false
        } else {
          throw new Error('Unexpected error response: ' + response.status)
        }
      })
      .catch((error) => {
        this.showUnexpectedErrorSnackbar(error)
      })
      .finally(() => {
        button.disabled = false
      })
  }

  showUnexpectedErrorSnackbar(error) {
    showSnackbar(
      '#share-snackbar',
      this.followerContainer.dataset.somethingWentWrongError +
        this.followerContainer.dataset.followError,
    )
    console.error('Updating followers failed: ' + error)
  }

  hideEmptyContainerMessage() {
    this.emptyContainerMessage.classList.remove('d-block')
    this.emptyContainerMessage.classList.add('d-none')
  }

  showEmptyContainerMessage() {
    this.emptyContainerMessage.classList.remove('d-none')
    this.emptyContainerMessage.classList.add('d-block')
  }

  registerFollowOnButtonClickEventListeners() {
    document.querySelectorAll('.follow-btn').forEach((button) => {
      button.addEventListener('click', (e) => {
        e.preventDefault()
        e.stopImmediatePropagation()
        this.follow(e.target, e.target.dataset.userId)
      })
    })
  }

  registerUnfollowOnButtonClickEventListeners() {
    document.querySelectorAll('.unfollow-btn').forEach((button) => {
      button.addEventListener('click', (e) => {
        e.preventDefault()
        e.stopImmediatePropagation()
        this.unfollow(
          e.target,
          e.target.dataset.userId,
          e.target.dataset.userName,
        )
      })
    })
  }
}

const followerOverview = new FollowerOverview()
followerOverview.registerEventListeners()
