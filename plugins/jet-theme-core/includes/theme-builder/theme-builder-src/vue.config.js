module.exports = {
  outputDir: '../assets/builder',
  publicPath: '/',
  filenameHashing: false,
  productionSourceMap: false,
  runtimeCompiler: true,
  chainWebpack: config => {
    config.optimization.delete('splitChunks')
    config.plugins.delete('html')
    config.plugins.delete('preload')
    config.plugins.delete('prefetch')
  },
  css: {
    loaderOptions: {
      sass: {}
    }
  },
}
