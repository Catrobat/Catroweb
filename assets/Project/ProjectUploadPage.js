import './ProjectUpload.scss'

document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.project-upload')
  if (!container) return

  const apiUrl = container.dataset.apiUrl
  const projectUrlTemplate = container.dataset.projectUrl

  const trans = {
    dropHere: container.dataset.transDropHere || 'Drag & drop your .catrobat file here',
    orBrowse: container.dataset.transOrBrowse || 'or click to browse',
    upload: container.dataset.transUpload || 'Upload',
    uploading: container.dataset.transUploading || 'Uploading...',
    uploadSuccess: container.dataset.transSuccess || 'Project uploaded successfully!',
    uploadError: container.dataset.transUploadError || 'Upload failed.',
    invalidFile: container.dataset.transInvalidFile || 'Please select a .catrobat file',
    unauthorized: container.dataset.transUnauthorized || 'You must be logged in.',
    rateLimited: container.dataset.transRateLimited || 'Too many uploads.',
    viewProject: container.dataset.transViewProject || 'View Project',
    uploadAnother: container.dataset.transUploadAnother || 'Upload Another',
  }

  const dropZone = document.getElementById('upload-drop-zone')
  const fileInput = document.getElementById('upload-file-input')
  const fileInfo = document.getElementById('upload-file-info')
  const fileName = document.getElementById('upload-file-name')
  const submitBtn = document.getElementById('upload-submit')
  const progressContainer = document.getElementById('upload-progress')
  const progressFill = document.getElementById('upload-progress-fill')
  const progressText = document.getElementById('upload-progress-text')
  const errorContainer = document.getElementById('upload-error')
  const errorMessage = document.getElementById('upload-error-text')
  const successContainer = document.getElementById('upload-success')

  // Initialize text from translations
  const dropText = dropZone.querySelector('.project-upload__drop-text')
  const browseText = dropZone.querySelector('.project-upload__browse-text')
  if (dropText) dropText.textContent = trans.dropHere
  if (browseText) browseText.textContent = trans.orBrowse
  submitBtn.textContent = trans.upload

  let selectedFile = null

  // Drag and drop
  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault()
    dropZone.classList.add('drag-over')
  })

  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('drag-over')
  })

  dropZone.addEventListener('drop', (e) => {
    e.preventDefault()
    dropZone.classList.remove('drag-over')
    const files = e.dataTransfer.files
    if (files.length > 0) {
      handleFile(files[0])
    }
  })

  dropZone.addEventListener('click', () => fileInput.click())

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      handleFile(fileInput.files[0])
    }
  })

  function handleFile(file) {
    if (!file.name.endsWith('.catrobat')) {
      showError(trans.invalidFile)
      return
    }
    selectedFile = file
    fileName.textContent = file.name + ' (' + formatSize(file.size) + ')'
    fileInfo.style.display = 'flex'
    submitBtn.style.display = 'inline-flex'
    submitBtn.disabled = false
    submitBtn.textContent = trans.upload
    hideError()
    successContainer.style.display = 'none'
  }

  submitBtn.addEventListener('click', () => {
    if (selectedFile) {
      uploadProject(selectedFile)
    }
  })

  function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B'
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB'
    return (bytes / 1048576).toFixed(1) + ' MB'
  }

  function showError(msg) {
    errorMessage.textContent = msg
    errorContainer.style.display = 'flex'
  }

  function hideError() {
    errorContainer.style.display = 'none'
  }

  function uploadProject(file) {
    hideError()
    submitBtn.disabled = true
    submitBtn.textContent = trans.uploading
    progressContainer.style.display = 'flex'
    progressFill.style.width = '0%'
    progressText.textContent = '0%'

    // Read file to compute MD5 checksum, then upload
    const reader = new FileReader()
    reader.onload = function () {
      const checksum = computeMd5Hex(new Uint8Array(reader.result))
      doUpload(file, checksum)
    }
    reader.onerror = function () {
      // If reading fails, try without checksum
      doUpload(file, '')
    }
    reader.readAsArrayBuffer(file)
  }

  function doUpload(file, checksum) {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('checksum', checksum)
    formData.append('flavor', 'pocketcode')
    formData.append('private', 'false')

    const xhr = new XMLHttpRequest()

    xhr.upload.addEventListener('progress', (e) => {
      if (e.lengthComputable) {
        const percent = Math.round((e.loaded / e.total) * 100)
        progressFill.style.width = percent + '%'
        progressText.textContent = percent + '%'
      }
    })

    xhr.addEventListener('load', () => {
      progressContainer.style.display = 'none'

      if (xhr.status === 201) {
        let response
        try {
          response = JSON.parse(xhr.responseText)
        } catch {
          response = {}
        }

        const projectId = response.project_id || response.id || ''
        const projectUrl = projectUrlTemplate
          ? projectUrlTemplate.replace('__ID__', projectId)
          : '/app/project/' + projectId

        const successText = document.getElementById('upload-success-text')
        const viewBtn = document.getElementById('upload-view-project')
        const anotherBtn = document.getElementById('upload-another')

        if (successText) successText.textContent = trans.uploadSuccess
        if (viewBtn) {
          viewBtn.href = projectUrl
          viewBtn.textContent = trans.viewProject
          viewBtn.style.display = projectId ? 'inline-block' : 'none'
        }
        if (anotherBtn) {
          anotherBtn.textContent = trans.uploadAnother
          anotherBtn.addEventListener('click', () => {
            successContainer.style.display = 'none'
            fileInfo.style.display = 'none'
            submitBtn.disabled = true
            submitBtn.textContent = trans.upload
            submitBtn.style.display = 'block'
            dropZone.style.display = 'flex'
            selectedFile = null
            fileInput.value = ''
          })
        }

        successContainer.style.display = 'block'

        dropZone.style.display = 'none'
        fileInfo.style.display = 'none'
        submitBtn.style.display = 'none'
      } else if (xhr.status === 401) {
        showError(trans.unauthorized)
        submitBtn.disabled = false
        submitBtn.textContent = trans.upload
      } else if (xhr.status === 429) {
        showError(trans.rateLimited)
        submitBtn.disabled = false
        submitBtn.textContent = trans.upload
      } else {
        let message = trans.uploadError
        try {
          const err = JSON.parse(xhr.responseText)
          if (err.error) message = err.error
          if (err.validation_errors) message = Object.values(err.validation_errors).join(', ')
        } catch {
          // ignore parse error
        }
        showError(message)
        submitBtn.disabled = false
        submitBtn.textContent = trans.upload
      }
    })

    xhr.addEventListener('error', () => {
      progressContainer.style.display = 'none'
      showError(trans.uploadError)
      submitBtn.disabled = false
      submitBtn.textContent = trans.upload
    })

    xhr.open('POST', apiUrl)
    xhr.withCredentials = true
    xhr.send(formData)
  }

  // Minimal MD5 implementation (RFC 1321)
  // Based on Joseph Myers' implementation, compacted for file checksums
  function computeMd5Hex(uint8Array) {
    function md5cycle(x, k) {
      let a = x[0],
        b = x[1],
        c = x[2],
        d = x[3]
      a = ff(a, b, c, d, k[0], 7, -680876936)
      d = ff(d, a, b, c, k[1], 12, -389564586)
      c = ff(c, d, a, b, k[2], 17, 606105819)
      b = ff(b, c, d, a, k[3], 22, -1044525330)
      a = ff(a, b, c, d, k[4], 7, -176418897)
      d = ff(d, a, b, c, k[5], 12, 1200080426)
      c = ff(c, d, a, b, k[6], 17, -1473231341)
      b = ff(b, c, d, a, k[7], 22, -45705983)
      a = ff(a, b, c, d, k[8], 7, 1770035416)
      d = ff(d, a, b, c, k[9], 12, -1958414417)
      c = ff(c, d, a, b, k[10], 17, -42063)
      b = ff(b, c, d, a, k[11], 22, -1990404162)
      a = ff(a, b, c, d, k[12], 7, 1804603682)
      d = ff(d, a, b, c, k[13], 12, -40341101)
      c = ff(c, d, a, b, k[14], 17, -1502002290)
      b = ff(b, c, d, a, k[15], 22, 1236535329)
      a = gg(a, b, c, d, k[1], 5, -165796510)
      d = gg(d, a, b, c, k[6], 9, -1069501632)
      c = gg(c, d, a, b, k[11], 14, 643717713)
      b = gg(b, c, d, a, k[0], 20, -373897302)
      a = gg(a, b, c, d, k[5], 5, -701558691)
      d = gg(d, a, b, c, k[10], 9, 38016083)
      c = gg(c, d, a, b, k[15], 14, -660478335)
      b = gg(b, c, d, a, k[4], 20, -405537848)
      a = gg(a, b, c, d, k[9], 5, 568446438)
      d = gg(d, a, b, c, k[14], 9, -1019803690)
      c = gg(c, d, a, b, k[3], 14, -187363961)
      b = gg(b, c, d, a, k[8], 20, 1163531501)
      a = gg(a, b, c, d, k[13], 5, -1444681467)
      d = gg(d, a, b, c, k[2], 9, -51403784)
      c = gg(c, d, a, b, k[7], 14, 1735328473)
      b = gg(b, c, d, a, k[12], 20, -1926607734)
      a = hh(a, b, c, d, k[5], 4, -378558)
      d = hh(d, a, b, c, k[8], 11, -2022574463)
      c = hh(c, d, a, b, k[11], 16, 1839030562)
      b = hh(b, c, d, a, k[14], 23, -35309556)
      a = hh(a, b, c, d, k[1], 4, -1530992060)
      d = hh(d, a, b, c, k[4], 11, 1272893353)
      c = hh(c, d, a, b, k[7], 16, -155497632)
      b = hh(b, c, d, a, k[10], 23, -1094730640)
      a = hh(a, b, c, d, k[13], 4, 681279174)
      d = hh(d, a, b, c, k[0], 11, -358537222)
      c = hh(c, d, a, b, k[3], 16, -722521979)
      b = hh(b, c, d, a, k[6], 23, 76029189)
      a = hh(a, b, c, d, k[9], 4, -640364487)
      d = hh(d, a, b, c, k[12], 11, -421815835)
      c = hh(c, d, a, b, k[15], 16, 530742520)
      b = hh(b, c, d, a, k[2], 23, -995338651)
      a = ii(a, b, c, d, k[0], 6, -198630844)
      d = ii(d, a, b, c, k[7], 10, 1126891415)
      c = ii(c, d, a, b, k[14], 15, -1416354905)
      b = ii(b, c, d, a, k[5], 21, -57434055)
      a = ii(a, b, c, d, k[12], 6, 1700485571)
      d = ii(d, a, b, c, k[3], 10, -1894986606)
      c = ii(c, d, a, b, k[10], 15, -1051523)
      b = ii(b, c, d, a, k[1], 21, -2054922799)
      a = ii(a, b, c, d, k[8], 6, 1873313359)
      d = ii(d, a, b, c, k[15], 10, -30611744)
      c = ii(c, d, a, b, k[6], 15, -1560198380)
      b = ii(b, c, d, a, k[13], 21, 1309151649)
      a = ii(a, b, c, d, k[4], 6, -145523070)
      d = ii(d, a, b, c, k[11], 10, -1120210379)
      c = ii(c, d, a, b, k[2], 15, 718787259)
      b = ii(b, c, d, a, k[9], 21, -343485551)
      x[0] = add32(a, x[0])
      x[1] = add32(b, x[1])
      x[2] = add32(c, x[2])
      x[3] = add32(d, x[3])
    }
    function cmn(q, a, b, x, s, t) {
      a = add32(add32(a, q), add32(x, t))
      return add32((a << s) | (a >>> (32 - s)), b)
    }
    function ff(a, b, c, d, x, s, t) {
      return cmn((b & c) | (~b & d), a, b, x, s, t)
    }
    function gg(a, b, c, d, x, s, t) {
      return cmn((b & d) | (c & ~d), a, b, x, s, t)
    }
    function hh(a, b, c, d, x, s, t) {
      return cmn(b ^ c ^ d, a, b, x, s, t)
    }
    function ii(a, b, c, d, x, s, t) {
      return cmn(c ^ (b | ~d), a, b, x, s, t)
    }
    function add32(a, b) {
      return (a + b) & 0xffffffff
    }
    function rhex(n) {
      const hex = '0123456789abcdef'
      let s = ''
      for (let j = 0; j < 4; j++)
        s += hex.charAt((n >> (j * 8 + 4)) & 0x0f) + hex.charAt((n >> (j * 8)) & 0x0f)
      return s
    }

    const n = uint8Array.length
    const state = [1732584193, -271733879, -1732584194, 271733878]
    let i
    for (i = 64; i <= n; i += 64) {
      const k = new Array(16)
      for (let j = 0; j < 64; j += 4)
        k[j >> 2] =
          uint8Array[i - 64 + j] |
          (uint8Array[i - 64 + j + 1] << 8) |
          (uint8Array[i - 64 + j + 2] << 16) |
          (uint8Array[i - 64 + j + 3] << 24)
      md5cycle(state, k)
    }
    const tail = new Array(16).fill(0)
    for (let j = 0; j < n - i + 64; j++) tail[j >> 2] |= uint8Array[i - 64 + j] << ((j % 4) << 3)
    tail[(n - i + 64) >> 2] |= 0x80 << (((n % 64) % 4) << 3)
    if (n % 64 > 55) {
      md5cycle(state, tail)
      tail.fill(0)
    }
    tail[14] = (n * 8) & 0xffffffff
    tail[15] = Math.floor((n * 8) / 4294967296)
    md5cycle(state, tail)

    return rhex(state[0]) + rhex(state[1]) + rhex(state[2]) + rhex(state[3])
  }
})
