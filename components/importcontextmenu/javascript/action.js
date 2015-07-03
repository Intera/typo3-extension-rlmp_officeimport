
TYPO3.Components.PageTree.Actions.rlmpOfficeImport = function(node, doktype) {

	TYPO3.Backend.ContentContainer.setUrl(
		'mod.php?M=xMOD_tx_rlmpofficeimport_cm1&' +
		'id=' + node.attributes.nodeData.id +
		'&doctype=' + doktype
	);
};

TYPO3.Components.PageTree.Actions.rlmpOfficeImport1 = function(node) {
	this.rlmpOfficeImport(node, 1);
};

TYPO3.Components.PageTree.Actions.rlmpOfficeImport2 = function(node) {
	this.rlmpOfficeImport(node, 2);
};

TYPO3.Components.PageTree.Actions.rlmpOfficeImport3 = function(node) {
	this.rlmpOfficeImport(node, 3);
};