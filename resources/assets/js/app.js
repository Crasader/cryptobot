
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Passport componets 
 */
Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue')
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue')
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue')
);


/**
 * Custom components
 */
Vue.component('balance', require('./components/Balance.vue'));
Vue.component('order', require('./components/Order.vue'));
Vue.component('trade', require('./components/Trade.vue'));
Vue.component('trade2', require('./components/Trade2.vue'));
Vue.component('trade3', require('./components/Trade3.vue'));
Vue.component('tradelist', require('./components/TradeList.vue'));
Vue.component('tradelist2', require('./components/TradeList2.vue'));
Vue.component('tradelist3', require('./components/TradeList3.vue'));
Vue.component('tradepanel', require('./components/TradePanel.vue'));
Vue.component('notification-list', require('./components/NotificationList.vue'));

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
const app = new Vue({
    el: '#app'
});

$(document).ready(function () {

    require('./partials/notifications');
    
	$.extend( $.fn.dataTable.defaults, {
	    responsive: true
	} );
	$('#myTable').DataTable();
	$('.myTable').DataTable();

});
