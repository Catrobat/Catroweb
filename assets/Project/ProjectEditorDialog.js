export class ProjectEditorDialog {
  constructor(titleText, confirmText, denyText) {
    this.titleText = titleText
    this.confirmText = confirmText
    this.denyText = denyText
  }

  async show(callback) {
    const { default: Swal } = await import('sweetalert2')
    Swal.fire({
      title: this.titleText,
      icon: 'question',
      showDenyButton: true,
      showCloseButton: true,
      allowOutsideClick: true,
      backdrop: true,
      confirmButtonText: this.confirmText,
      denyButtonText: this.denyText,
    }).then(callback)
  }
}
