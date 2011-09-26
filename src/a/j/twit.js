new TWTR.Widget({
  version: 2,
  type: 'profile',
  rpp: 3,
  interval: 60000,
  width: 520,
  height: 160,
  theme: {
	shell: {
	  background: '#ffffff',
	  color: '#333333'
	},
	tweets: {
	  background: '#ffffff',
	  color: '#333333',
	  links: '#4aed05'
	}
  },
  features: {
	scrollbar: false,
	loop: false,
	live: true,
	hashtags: true,
	timestamp: true,
	avatars: false,
	behavior: 'all'
  }
}).render().setUser('AsterixCMS').start();
