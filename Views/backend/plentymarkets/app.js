// {namespace name=backend/Plentymarkets}
// {block name=backend/Plentymarkets/application}
Ext.define('Shopware.apps.Plentymarkets', {

	name: 'Shopware.apps.Plentymarkets',
	extend: 'Enlight.app.SubApplication',
	loadPath: '{url action=load}',
	bulkLoad: true,

	controllers: [
	    'Main',
	    'Mapping',
	    'Settings',
	    'Export'
	],

	views: [
	    'Api',
	    'dx.Continuous',
	    'dx.Initial',
	    'Export',
	    'log.Grid',
	    'log.Main',
	    'Main',
	    'mapping.Tab',
	    'mapping.Main',
	    'Settings',
	    'Start'
	],

	stores: [
	    'dx.Continuous',
	    'Export',
	    'Log',
	    'mapping.Plentymarkets',
	    'mapping.Resource',
	    'mapping.Shopware',
	    'mapping.Status',
	    'Multishop',
	    'OrderMarking',
	    'Orderstatus',
	    'outgoing_items.Interval',
	    'outgoing_items.OutgoingItems',
	    'Producer',
	    'Referrer',
	    'settings.Batch',
	    'Settings',
	    'Warehouse'
	],

	models: [
	    'dx.Continuous',
	    'Export',
	    'Log',
	    'mapping.Plentymarkets',
	    'mapping.Shopware',
	    'mapping.Status',
	    'Multishop',
	    'Orderstatus',
	    'Producer',
	    'Referrer',
	    'settings.Batch',
	    'Settings',
	    'Warehouse'
	],

	launch: function()
	{
		var me = this, mainController = me.getController('Main');

		return mainController.mainWindow;
	}
});
// {/block}