const { test, expect } = require('@playwright/test')

test.describe('web/general help redirects', () => {
  test('redirects English help requests to the wiki', async ({ request }) => {
    const response = await request.get('/app/help', {
      headers: {
        Cookie: 'hl=en',
      },
      maxRedirects: 0,
    })

    expect(response.status()).toBe(302)
    expect(response.headers().location).toBe('https://catrobat.org/docs/')
  })

  test('redirects German help requests to the German wiki', async ({ request }) => {
    const response = await request.get('/app/help', {
      headers: {
        Cookie: 'hl=de_DE',
      },
      maxRedirects: 0,
    })

    expect(response.status()).toBe(302)
    expect(response.headers().location).toBe('https://catrobat.org/de/dokumentation/')
  })
})
