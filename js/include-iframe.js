; (function () {
  // URL of your site
  const yourSite = 'http://localhost/j6/moodle'

  // Path to the iframe-resizer child script
  const childScript = 'node_modules/@iframe-resizer/child/index.umd.min.js'

  // Options for iframe-resizer  (See Child Page API for more details)
  window.iframeResizer = {
    targetOrigin: yourSite,
  }

  // Load iframe-resizer child script
  const script = document.createElement('script')
  script.src = `${yourSite}/${childScript}`
  script.async = true
  script.fetchPriority = 'high'
  script.onerror = function () {
    console.error('Failed to load iframe-resizer library')
  }

  // Insert iframe-resizer child after this script
  document.currentScript.after(script)
})()
