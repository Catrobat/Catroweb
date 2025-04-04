module.exports = {
  presets: [
    [
      '@babel/preset-env',
      {
        useBuiltIns: 'usage', // Only includes the polyfills you need based on the usage
        corejs: 3, // Use core-js version 3 for polyfilling
      },
    ],
  ],
  plugins: [
    '@babel/plugin-proposal-class-properties', // Add the class properties plugin
  ],
}
