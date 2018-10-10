pimcore.registerNS("pimcore.plugin.LicensesBundle");

pimcore.plugin.LicensesBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.LicensesBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        // alert("LicensesBundle ready!");
    }
});

var LicensesBundlePlugin = new pimcore.plugin.LicensesBundle();
