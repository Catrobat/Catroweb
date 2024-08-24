import './Slider.scss'

const iconData = [
  { id: 1, icon: 'error', caption: 'Error' },
  { id: 2, icon: 'warning', caption: 'Warning' },
  { id: 3, icon: 'notifications', caption: 'Notifications' },
  { id: 4, icon: 'error_outline', caption: 'Outlined Error' },
  { id: 5, icon: 'build', caption: 'Construction and Maintenance' },
  { id: 6, icon: 'settings', caption: 'Adjusting Settings' },
  { id: 7, icon: 'tune', caption: 'Fine-Tuning Equipment' },
  { id: 8, icon: 'autorenew', caption: 'Renewal and Updating' },
  { id: 9, icon: 'cached', caption: 'Data Caching' },
  { id: 10, icon: 'bug_report', caption: 'Bug Reporting and Fixing' },
  { id: 11, icon: 'track_changes', caption: 'Tracking Changes' },
  { id: 12, icon: 'vpn_key', caption: 'Security Key Management' },
  { id: 13, icon: 'timeline', caption: 'Maintenance Schedule' },
  { id: 14, icon: 'info', caption: 'Information' },
  { id: 15, icon: 'info_outline', caption: 'Outlined Information' },
  { id: 16, icon: 'announcement', caption: 'Announcement' },
]

const iconList = document.getElementById('iconList')
iconData.forEach((data) => {
  const li = document.createElement('li')
  li.innerHTML = `
                <label>
                    <i class="material-icons">${data.icon}</i>
                    <p>${data.caption}</p>
                </label>
            `
  iconList.appendChild(li)
})
