/**
 * Lenz+ studio panel: service worker (served at <panel>/sw/, scoped to the
 * panel). The panel's page shows the notifications through it, because
 * installed web apps on phones can only show them from a worker; tapping one
 * focuses an open panel window on the item's page, or opens it. Nothing is
 * cached: the panel is always loaded fresh from the site.
 */
/* eslint-env serviceworker */
self.addEventListener( 'install', () => self.skipWaiting() );

self.addEventListener( 'activate', ( event ) => event.waitUntil( self.clients.claim() ) );

self.addEventListener( 'notificationclick', ( event ) => {
	const url = ( event.notification.data && event.notification.data.url ) || self.registration.scope;
	event.notification.close();

	event.waitUntil(
		self.clients.matchAll( { type: 'window', includeUncontrolled: true } ).then( ( windows ) => {
			const open = windows.find( ( client ) => client.url.indexOf( self.registration.scope ) === 0 );
			if ( open ) {
				return open.navigate( url ).then( ( client ) => ( client || open ).focus() );
			}

			return self.clients.openWindow( url );
		} )
	);
} );
