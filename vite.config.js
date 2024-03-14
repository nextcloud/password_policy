import { createAppConfig } from '@nextcloud/vite-config'

export default createAppConfig({
	settings: 'src/settings.js',
}, {
	// Do not inline to speed up browser parsing JS code
	inlineCSS: false,
})
