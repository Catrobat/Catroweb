import Swal from 'sweetalert2'
import '../Components/TabBar'
import './Profile.scss'
import { showSnackbar, SnackbarDuration } from '../Layout/Snackbar'
import { escapeAttr, escapeHtml } from '../Components/HtmlEscape'
import { getImageUrl } from '../Layout/ImageVariants'

document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.js-follower-overview')
  if (!container) return

  const userId = container.dataset.userId
  const baseUrl = container.dataset.baseUrl || ''
  const theme = container.dataset.theme || 'pocketcode'
  const loginUrl = container.dataset.loginUrl
  const userRole = container.dataset.userRole || 'guest'

  const trans = {
    somethingWentWrong: container.dataset.somethingWentWrongError,
    followError: container.dataset.followError,
    unfollowError: container.dataset.unfollowError,
    accountNotVerified: container.dataset.accountNotVerified,
    accountSuspended: container.dataset.accountSuspended,
    rateLimited: container.dataset.rateLimited,
    unfollowButton: container.dataset.unfollowButton,
    unfollowQuestion: container.dataset.unfollowQuestion,
    cancelButton: container.dataset.cancelButton,
    follows: container.dataset.transFollows,
    follow: container.dataset.transFollow,
    followsMe: container.dataset.transFollowsMe,
  }

  function showVerificationAlert(message) {
    Swal.fire({
      text: message,
      icon: 'warning',
      customClass: { confirmButton: 'btn btn-primary' },
      buttonsStyling: false,
    })
  }

  function renderFollowerCard(user, showFollowsMe, itemClassPrefix) {
    const avatarSrc = getImageUrl(
      user.avatar,
      'thumb',
      baseUrl + '/images/default/avatar_default-thumb@1x.webp',
    )
    const profileUrl = baseUrl + '/' + escapeAttr(theme) + '/user/' + escapeAttr(user.id)
    const itemClass = itemClassPrefix + '-' + escapeAttr(user.id)

    let followsMeHtml = ''
    if (showFollowsMe && user.follows_you) {
      followsMeHtml = `
        <div class="d-inline">
          <i class="material-icons info-icon">done</i> ${escapeHtml(trans.followsMe)}
        </div>`
    }

    let buttonHtml = ''
    if (userRole !== 'guest') {
      if (user.is_following) {
        buttonHtml = `
          <button class="btn btn-outline-primary btn-block unfollow-btn mt-0"
                  data-user-id="${escapeAttr(user.id)}" data-user-name="${escapeAttr(user.username)}">
            ${escapeHtml(trans.follows)}
          </button>`
      } else {
        buttonHtml = `
          <button class="btn btn-primary btn-block follow-btn mt-0"
                  data-user-id="${escapeAttr(user.id)}">
            ${escapeHtml(trans.follow)}
          </button>`
      }
    }

    return `
      <div class="col-12 my-3 ${itemClass}">
        <div class="follower-item">
          <div class="follower-item__avatar">
            <a href="${profileUrl}">
              <img class="round" src="${escapeAttr(avatarSrc)}" alt="">
            </a>
          </div>
          <div class="follower-item__text">
            <a href="${profileUrl}">
              <span class="h4">${escapeHtml(user.username)}</span>
              <div class="text-muted d-flex gap-3">
                <span><i class="material-icons" style="font-size: inherit; vertical-align: middle; margin-right: 2px;">code</i> ${user.project_count ?? 0}</span>
                ${user.follower_count !== undefined ? '<span><i class="material-icons" style="font-size: inherit; vertical-align: middle; margin-right: 2px;">people</i> ' + user.follower_count + '</span>' : ''}
              </div>
              <div class="text-muted text-uppercase follower-item__info">
                ${followsMeHtml}
              </div>
            </a>
          </div>
          ${buttonHtml ? `<div class="follower-item__action">${buttonHtml}</div>` : ''}
        </div>
      </div>`
  }

  function updateTabCounts(totalFollowers, totalFollowing) {
    const followersCount = document.getElementById('followers-count')
    const followingCount = document.getElementById('following-count')
    if (followersCount) followersCount.textContent = totalFollowers
    if (followingCount) followingCount.textContent = totalFollowing
  }

  function removeSkeletons(container) {
    container.querySelectorAll('.js-skeleton').forEach((el) => el.remove())
  }

  function renderList(cardsContainer, emptyContainer, users, showFollowsMe, itemClassPrefix) {
    removeSkeletons(cardsContainer)
    if (users.length === 0) {
      emptyContainer.classList.remove('d-none')
      emptyContainer.classList.add('d-block')
    } else {
      emptyContainer.classList.remove('d-block')
      emptyContainer.classList.add('d-none')
      users.forEach((user) => {
        cardsContainer.insertAdjacentHTML(
          'beforeend',
          renderFollowerCard(user, showFollowsMe, itemClassPrefix),
        )
      })
    }
  }

  function loadFollowData() {
    const followerCards = document.getElementById('follower-cards')
    const followingCards = document.getElementById('following-cards')
    const noFollowers = document.getElementById('no-followers')
    const noFollowing = document.getElementById('no-following')

    if (!followerCards || !followingCards) return

    const followersUrl = baseUrl + '/api/users/' + userId + '/followers'
    const followingUrl = baseUrl + '/api/users/' + userId + '/following'

    Promise.all([
      fetch(followersUrl, { credentials: 'same-origin' }).then((r) => {
        if (!r.ok) throw new Error('HTTP ' + r.status)
        return r.json()
      }),
      fetch(followingUrl, { credentials: 'same-origin' }).then((r) => {
        if (!r.ok) throw new Error('HTTP ' + r.status)
        return r.json()
      }),
    ])
      .then(([followersData, followingData]) => {
        updateTabCounts(followersData.total_followers, followersData.total_following)
        renderList(followerCards, noFollowers, followersData.data || [], false, 'follower-item')
        renderList(followingCards, noFollowing, followingData.data || [], false, 'following-item')
      })
      .catch((error) => {
        console.error('Failed to load follower data:', error)
        removeSkeletons(followerCards)
        removeSkeletons(followingCards)
        showSnackbar('#share-snackbar', trans.somethingWentWrong, SnackbarDuration.error)
      })
  }

  function updateProfileHeaderButtons(isFollowing) {
    const followBtn = document.querySelector('.profile-follow')
    const followsBtn = document.querySelector('.profile-follows')
    if (followBtn) followBtn.style.display = isFollowing ? 'none' : 'block'
    if (followsBtn) followsBtn.style.display = isFollowing ? 'block' : 'none'
  }

  function handleFollow(targetUserId) {
    if (userRole === 'guest') {
      window.location.href = loginUrl
      return
    }

    const url = baseUrl + '/api/users/' + targetUserId + '/follow'
    fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
      .then((response) => {
        if (response.ok) {
          updateProfileHeaderButtons(true)
          loadFollowData()
        } else if (response.status === 401) {
          window.location.href = loginUrl
        } else if (response.status === 429) {
          showSnackbar(
            '#share-snackbar',
            trans.rateLimited || "You're following/unfollowing too quickly. Please wait a moment.",
          )
        } else if (response.status === 403) {
          return response
            .json()
            .then((body) => {
              if (body?.error?.message === 'Email verification required.') {
                showVerificationAlert(trans.accountNotVerified)
              } else if (body?.error?.message === 'Your account has been suspended.') {
                showVerificationAlert(trans.accountSuspended)
              } else {
                showSnackbar(
                  '#share-snackbar',
                  trans.somethingWentWrong + trans.followError,
                  SnackbarDuration.error,
                )
              }
            })
            .catch(() =>
              showSnackbar(
                '#share-snackbar',
                trans.somethingWentWrong + trans.followError,
                SnackbarDuration.error,
              ),
            )
        } else {
          throw new Error('Request failed with status:' + response.status)
        }
      })
      .catch((error) => {
        showSnackbar(
          '#share-snackbar',
          trans.somethingWentWrong + trans.followError,
          SnackbarDuration.error,
        )
        console.error('Follow failed:', error)
      })
  }

  function handleUnfollow(targetUserId, username) {
    Swal.fire({
      title: trans.unfollowQuestion,
      icon: 'question',
      showCancelButton: true,
      allowOutsideClick: false,
      confirmButtonText: trans.unfollowButton.replace('%username%', username),
      cancelButtonText: trans.cancelButton,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
    }).then((result) => {
      if (result.value) {
        const url = baseUrl + '/api/users/' + targetUserId + '/unfollow'
        fetch(url, {
          method: 'DELETE',
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
        })
          .then((response) => {
            if (response.ok || response.status === 204) {
              updateProfileHeaderButtons(false)
              loadFollowData()
            } else if (response.status === 401) {
              window.location.href = loginUrl
            } else if (response.status === 429) {
              showSnackbar(
                '#share-snackbar',
                trans.rateLimited ||
                  "You're following/unfollowing too quickly. Please wait a moment.",
              )
            } else if (response.status === 403) {
              return response
                .json()
                .then((body) => {
                  if (body?.error?.message === 'Email verification required.') {
                    showVerificationAlert(trans.accountNotVerified)
                  } else if (body?.error?.message === 'Your account has been suspended.') {
                    showVerificationAlert(trans.accountSuspended)
                  } else {
                    showSnackbar(
                      '#share-snackbar',
                      trans.somethingWentWrong + trans.unfollowError,
                      SnackbarDuration.error,
                    )
                  }
                })
                .catch(() =>
                  showSnackbar(
                    '#share-snackbar',
                    trans.somethingWentWrong + trans.unfollowError,
                    SnackbarDuration.error,
                  ),
                )
            } else {
              throw new Error('Request failed with status:' + response.status)
            }
          })
          .catch((error) => {
            showSnackbar(
              '#share-snackbar',
              trans.somethingWentWrong + trans.unfollowError,
              SnackbarDuration.error,
            )
            console.error('Unfollow failed:', error)
          })
      }
    })
  }

  // Event delegation for follow/unfollow buttons
  document.querySelector('.tab-content')?.addEventListener('click', (e) => {
    const followBtn = e.target.closest('.follow-btn')
    if (followBtn) {
      e.preventDefault()
      e.stopImmediatePropagation()
      handleFollow(followBtn.dataset.userId)
      return
    }

    const unfollowBtn = e.target.closest('.unfollow-btn')
    if (unfollowBtn) {
      e.preventDefault()
      e.stopImmediatePropagation()
      handleUnfollow(unfollowBtn.dataset.userId, unfollowBtn.dataset.userName)
    }
  })

  // Handle follow/unfollow buttons in profile header via event delegation
  // (buttons may be rendered dynamically by ProfilePage.js after DOMContentLoaded)
  document.querySelector('.profile')?.addEventListener('click', (e) => {
    const followBtn = e.target.closest('.profile-follow.follow-btn')
    if (followBtn) {
      e.preventDefault()
      e.stopImmediatePropagation()
      handleFollow(followBtn.dataset.userId)
      return
    }

    const unfollowBtn = e.target.closest('.profile-follows.unfollow-btn')
    if (unfollowBtn) {
      e.preventDefault()
      e.stopImmediatePropagation()
      handleUnfollow(unfollowBtn.dataset.userId, unfollowBtn.dataset.userName)
    }
  })

  // Skip follower data loading if either user is a minor
  const profileContainer = document.querySelector('.js-profile')
  const viewerIsMinor = profileContainer?.dataset.viewerIsMinor === 'true'
  const profileIsMinor = profileContainer?.dataset.profileIsMinor === 'true'
  if (!viewerIsMinor && !profileIsMinor) {
    loadFollowData()
  }
})
