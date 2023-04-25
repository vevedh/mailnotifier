/**
 * @copyright Copyright (c) 2020 Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @license AGPL-3.0-or-later
 */

import FlowNotify from './views/FlowMailNotify'

window.OCA.WorkflowEngine.registerOperator({
	id: 'OCA\\FlowMailNotifications\\Flow\\Operation',
	color: '#f1d340',
	operation: '',
	options: FlowMailNotify,
})
