/**
 * @copyright Copyright (c) 2020 Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @license AGPL-3.0-or-later
 */

import FlowMailNotify from './views/FlowMailNotify'
import Vue from 'vue';

import VueTrix from "vue-trix";

Vue.use(VueTrix);

window.OCA.WorkflowEngine.registerOperator({
	id: 'OCA\\MailNotifier\\Flow\\Operation',
	color: '#f1d340',
	operation: '',
	options: FlowMailNotify,
})
