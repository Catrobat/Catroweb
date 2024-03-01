const categories = document.querySelectorAll('.category')

categories.forEach((category) => {
  const header = category.querySelector('.header')
  header.addEventListener('click', () => {
    category.classList.toggle('active')
  })
})
