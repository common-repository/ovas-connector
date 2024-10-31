"use strict";
var selects   = [];
var reloading = [];

function addFieldLink(datasource, id, datasourceid, apiFieldName = "NO API FIELDNAME SUPPLIED", hasSubFields = false, isRequired = false, hasApiCallback = false, linkedto = null) {
	// Get template
	let template = document.getElementById( "tabSectionAddField" + id );

	// Add a copy of the template to the page
	let clone = template.content.cloneNode( true );

	// Set api field name
	clone.querySelector( '[name="apiFieldName"]' ).value            = apiFieldName;
	clone.querySelector( '[name="apiLinkedToField"]' ).value        = linkedto;
	clone.querySelector( '[name="apiFieldNameDisplay"]' ).innerHTML = apiFieldName;

	// Do not show the 'remove link' icon if the field is required
	clone.querySelector( '.removeFieldLink' ).style.visibility = (isRequired ? 'hidden' : 'visible');

	// If there is no api callback , hide that dropdown option
	if (hasApiCallback == false) {
		let node = clone.querySelector( '[name="datasourcefields"]' ).querySelector( 'option[value="APIFIELD_FROMAPI"]' );
		if (node != null) {
			node.parentNode.removeChild( node );
		}
	}

	// Add the actual link selectors to the page
	document.querySelector( '#section_' + id + ' [name="apiLink' + datasource + '"] .fieldLinks' ).appendChild( clone );

	let addedElem = document.querySelector( '#section_' + id + ' [name="apiLink' + datasource + '"] .fieldLinks' ).querySelector( '[name="apiFieldName"][value="' + apiFieldName + '"]' );

	if (hasSubFields) {
		addSubFields( addedElem, apiFieldName );
	} else {
		if (addedElem.parentElement.querySelector( 'select[name="subfieldobjectmultilink"]' )) {
			addedElem.parentElement.querySelector( 'select[name="subfieldobjectmultilink"]' ).remove();
		}
	}

	let popup = document.querySelector( '#section_' + id + ' [name="apiLink' + datasource + '"] .apisourcelinks .popup' );
	popup.classList.add( 'hidden' );

	// Add changed event listener to the 'value from api' dropdown
	let valueFromApiElem = addedElem.parentElement.querySelector( 'select[name="datasourceStaticValueFromApi"]' );
	if (valueFromApiElem) {
		valueFromApiElem.addEventListener(
			'change',
			function(){
				let dropdownToUpdateName = this.closest( '.fieldLink' ).querySelector( 'input[name="apiFieldName"]' ).value;
				if (document.querySelector( '[name="apiLinkedToField"][value="' + dropdownToUpdateName + '"]' )) {
					let dropdownToUpdate = document.querySelector( '[name="apiLinkedToField"][value="' + dropdownToUpdateName + '"]' ).closest( '.fieldLink' ).querySelector( '[name="datasourcefields"]' );
					dataSourceChanged( dropdownToUpdate );
				}
			}
		);
	}

	// Tailify datasource dropdown
	let tDatasource = tail.select(
		addedElem.parentElement.querySelectorAll( '.datasourceFieldLink' ),
		{
			search: true,
			descriptions: true,
			hideSelected: true,
			hideDisabled: true,
			multiLimit: 5,
			multiShowCount: false,
			multiContainer: true
		}
	);

	if ( ! selects) {
		selects = [];
	}

	selects.push( tDatasource );

	// Tailify 'API value' dropdown
	let tApi = tail.select(
		addedElem.parentElement.querySelectorAll( 'select.datasourceStaticValueFromApi' ),
		{
			search: true
		}
	);
	selects.push( tApi );

	// Select the most logical default dropdown option
	let dataSourceDropdown = addedElem.parentElement.querySelector( '[name="datasourcefields"]' );
	// Check if the dropdown currently has nothing selected
	if ( ! dataSourceDropdown.value) {
		// If the datasource dropdown contains 'APIFIELD_FROMAPI', select that as default option
		if (optionExists( dataSourceDropdown, 'APIFIELD_FROMAPI' )) {
			dataSourceDropdown.value = 'APIFIELD_FROMAPI';
			dataSourceChanged( dataSourceDropdown );
		}

		// If the datasource has an option that matches the api field, select that as default
		if (optionExists( dataSourceDropdown, apiFieldName )) {
			dataSourceDropdown.value = apiFieldName;
		}
		refreshDropdown( dataSourceDropdown );
	}
}

function addAllRequiredFieldLinks(elem, apisource, id, datasourceid) {
	let popup = elem.closest( '.apisourcelinks' ).querySelector( '.addFieldLink:first-child .popup' );
	for (const linky of popup.querySelectorAll( 'a[data-required="1"]' ) ) {
		if (document.querySelector( '#section_' + id + ' [name="apiLink' + apisource + '"] .fieldLinks .fieldLink input[name="apiFieldName"][value="' + linky.dataset.id + '"]' ) == null) {
			addFieldLink( apisource, id, datasourceid, linky.dataset.id, linky.dataset.hassubfields, true, linky.dataset.hasapicallback, linky.dataset.linkedto );
		}
	}
}

function openAddFieldLinkPopup (elem) {
	let popup = elem.parentElement.querySelector( '.popup' );
	updateAddableFields( popup );
	popup.classList.toggle( 'hidden' );
}

function updateAddableFields(popup) {
	let parent = event.currentTarget.closest( '.addFieldLink' );

	for (const linky of popup.querySelectorAll( 'a' ) ) {
		linky.classList.remove( 'disabled' );
		if (document.querySelector( '#section_' + parent.dataset.sectionid + ' [name="apiLink' + parent.dataset.sourcename + '"] .fieldLinks .fieldLink input[name="apiFieldName"][value="' + linky.dataset.id + '"]' ) != null) {
			linky.classList.add( 'disabled' );
		}
	}
}

function removeFieldLink(link) {
	link.parentElement.remove();
}

function updateLinks() {
	let arr_datasource = [];		// Total array
	let arr_sections   = [];		// Section array (per form etc)
	let arr_apisources = [];        // Api sources
	let arr_fieldmaps  = [];		// Actual linked fields
	let json           = null;

	// Datasources
	var datasources = document.querySelectorAll( '#frm_ovas_connect .datasource' ).forEach(
		function(datasource) {
			arr_datasource = {};
			let id         = datasource.id;

			// WP settings field to store the plugin settings for this datasource in
			let field = document.getElementById( "ovas_fieldlinks_" + id );

			// Sections
			arr_sections = [];
			datasource.querySelectorAll( "section" ).forEach(
				function(section) {
					let formid = section.dataset.formid;

					if (section.querySelector( 'input#toggle' + formid ).checked) {
						// Technically redundant, because inactive sections won't be saved anyways
						// But this might be useful in the future, so we will keep it for now
						let section_enabled = section.querySelector( 'input#toggle' + formid ).checked ?? false;

						let arr_fieldmaps  = [];
						let arr_apisources = [];

						// Apisources
						var apisource = section.querySelectorAll( ".addApiLinks" ).forEach(
							function(source) {
								arr_fieldmaps = [];

								let apisourcename = source.dataset.sourcename;

								if (source.querySelector( ".apisourcename input.toggle" ).checked) {
									arr_fieldmaps = updateLinksForSource( source );
								}
								arr_apisources.push(
									{'id': source.dataset.sourcename,
										'fieldMaps': arr_fieldmaps
									}
								)
							}
						);

						arr_sections.push(
							{'id': formid,
								'label': formid,
								'enabled': section_enabled,
								'apisources': arr_apisources
							}
						);
					}
				}
			);
			arr_datasource[id]                              = {'id': id, 'sections': arr_sections};
			arr_datasource['ovas_connect_settings_version'] = '1';
			json        = JSON.stringify( arr_datasource );
			field.value = json;
			console.log(json);
		}
	);
}

function updateLinksForSource(source, isSubField = null) {
	let arr_fieldmaps = [];

	let selector = ".apisourcelinks > .fieldLinks > .fieldLink";
	if (isSubField) {
		selector = ".fieldLinkContainer.subfields .fieldLink";
	}

	// Fieldmaps
	source.querySelectorAll( selector ).forEach(
		function(fieldMap) {
			let subFields       = null;
			let multiLinkField  = null;
			let arr_fieldItem   = [];
			let datasourceField = fieldMap.querySelector( "select[name='datasourcefields']" );
			let datasourceValue = null;
			let datasourceLabel = null;

			// Tail.select doesn't always properly deselect the default empty option
			// after selecting a new value on a dynamically added select list.
			// Clear any empty select options
			let so = datasourceField.selectedOptions;

			for (let op of so) {
				if (op.value == '') {
					op.selected = false;
				}
			}

			// Loop through all selected fieldLinks options
			let fields = Array.from( datasourceField.querySelectorAll( "option:checked" ), e => e.value );

			// Sort fields based on order the user has selected
			let order = Array.from( datasourceField.parentElement.querySelectorAll( ".select-label.tail-select-container .select-handle" ), e => e.dataset.key );
			fields.sort(
				function(a, b) {
					return order.indexOf( a ) - order.indexOf( b );
				}
			);

			let apiField = fieldMap.querySelector( "input[name='apiFieldName']" );

			let i = 0;
			for (const field of fields) {
				// Subitem fieldMap
				if (fieldMap.dataset.type == 'subfield') {
					datasourceValue = updateLinksForSource( fieldMap, true );
				} else {
					// Normal fieldMap
					if (field == 'APIFIELD_STATIC') {
						datasourceValue = fieldMap.querySelector( "input[name='datasourceStaticValue']" ).value;
					} else if (field == 'APIFIELD_FROMAPI') {
						datasourceValue = fieldMap.querySelector( "select[name='datasourceStaticValueFromApi']" ).value;
						if (fieldMap.querySelector( "select[name='datasourceStaticValueFromApi']" ).options[fieldMap.querySelector( "select[name='datasourceStaticValueFromApi']" ).selectedIndex]) {
							datasourceLabel = fieldMap.querySelector( "select[name='datasourceStaticValueFromApi']" ).options[fieldMap.querySelector( "select[name='datasourceStaticValueFromApi']" ).selectedIndex].innerHTML;
						}
					} else {
						datasourceValue = null;
					}
				}

				if (typeof datasourceValue === 'object' && datasourceValue !== null) {
					let multilinkfielditem = fieldMap.querySelector( "select[name='subfieldobjectmultilink']" );
					// arr_fieldItem['SUBFIELD'] = datasourceValue;
					subFields = datasourceValue;
					if (multilinkfielditem) {
						multiLinkField = multilinkfielditem.options[multilinkfielditem.selectedIndex].value ?? null;
					}
				} else {

				}

				arr_fieldItem.push(
					{'key': field,
						'value': datasourceValue,
						'label': datasourceLabel,
						'order': i++
					}
				);
			}

			arr_fieldmaps.push(
				{'id': apiField.value,
					'apiFieldName': apiField.value,
					'mappedFieldItems': arr_fieldItem,
					'isSubField': (subFields != null),
					'subFieldMap': subFields,
					'multiLinkField': multiLinkField
				}
			);
		}
	);
	return arr_fieldmaps;
}

function apiSourceLinksToggle(elem) {
	elem.parentElement.parentElement.querySelector( '.apisourcelinks' ).style.display = elem.checked ? 'block' : 'none';
}

function dataSourceToggle(elem) {
	elem.parentElement.parentElement.parentElement.querySelector( '.sectionLinks' ).style.display = elem.checked ? 'block' : 'none';
}

function dataSourceChanged(elem, openAfterRefresh = false) {
	elem.closest( '.fieldLink, .subfieldLink' ).querySelector( 'input[name="datasourceStaticValue"]' ).style.display = (Array.from( elem.querySelectorAll( "option:checked" ),e => e.value ).includes( 'APIFIELD_STATIC' ) ? 'block' : 'none' );
	elem.closest( '.fieldLink, .subfieldLink' ).querySelector( '.datasourceStaticValueFromApi' ).style.display       = (Array.from( elem.querySelectorAll( "option:checked" ),e => e.value ).includes( 'APIFIELD_FROMAPI' ) ? 'block' : 'none' );

	let subFieldname = null;
	// Is this a subField?
	if (subFieldname = elem.closest( '.fieldLinkContainer.subfields' )) {
		subFieldname = elem.closest( '.fieldLinkContainer.subfields' ).dataset.name;
	}

	// Get possible static values from API
	// if (elem.value == 'APIFIELD_FROMAPI') {
	if ([...elem.selectedOptions].map( el => el.value ).includes( 'APIFIELD_FROMAPI' )) {
		let apiLinkedValue     = null;
		let apiLinkedFieldName = elem.closest( '.fieldLink, .subfieldLink' ).querySelector( '[name="apiLinkedToField"]' ).value;
		if (apiLinkedFieldName != null) {
			let apiLinkedSubFieldName = document.querySelector( '[name="apiFieldName"][value="' + apiLinkedFieldName + '"]' )

			// Get the value from the linked field dropdown so we can get the values for the subfield dropdown
			if (apiLinkedSubFieldName != null ) {
				apiLinkedValue = apiLinkedSubFieldName.closest( '.fieldLink' ).querySelector( '[name="datasourceStaticValueFromApi"]' ).value;

				// If the dropdown has no value (eg: initial page load and the dropdown ajax call has not completed yet)
				// Take the value from the static value field (the saved value)
				if ( ! apiLinkedValue) {
					apiLinkedValue = apiLinkedSubFieldName.closest( '.fieldLink' ).querySelector( '[name="datasourceStaticValue"]' ).value;
				}
			}
		}
		let selectedValue = elem.closest( '.datalink' ).querySelector( '[name="datasourceStaticValueFromApi"]' ).value;

		getStaticValuesFromApi(
			elem.closest( '.fieldLink, .subfieldLink' ).querySelector( 'select[name="datasourceStaticValueFromApi"]' ),
			elem.closest( '.addApiLinks' ).dataset.sourcename,
			elem.closest( '.fieldLink, .subfieldLink' ).querySelector( '[name="apiFieldName"]' ).value,
			subFieldname,
			apiLinkedFieldName,
			apiLinkedValue,
			selectedValue,
			openAfterRefresh
		);
	}
}

// If there is a template for this api field, use the instead of the default
// datasource option dropdown
function addSubFields(elem, apiFieldName) {
	// Remove existing fields
	elem.parentElement.querySelector( '.datalink .fieldLinkContainer' ).remove();
	let t = elem.closest( '.addApiLinks' ).querySelector( '.apiSourceSubFields[data-name="' + apiFieldName + '"]' );

	// Clone template so we can add it to the page
	var clone = t.content.cloneNode( true );

	// Add subfields from template
	elem.closest( '.fieldLink' ).querySelector( '.datalink' ).append( clone );

	// Flag fieldLink as subfield
	elem.closest( '.fieldLink' ).dataset.type = 'subfield';
}

function addSectionApiFieldDropdownOptions(elem, apielem) {
	let apival      = apielem.options[apielem.selectedIndex].value;
	let apivalgroup = apielem.options[apielem.selectedIndex].parentNode.label;
	elem.querySelectorAll( '.apisourcelinks select[name="apifields"]' ).forEach(
		e => {
        e.querySelector( 'optgroup[label="' + apivalgroup + '"]' ).appendChild( new Option( apival, apival ) );
		}
	);
}


function resubmitApiCall(id) {
	var data = {
		'action': 'resubmit_api_call',
		'id': id,
		'nonce': ajax_var.nonce
	};

	jQuery.post(
		ajaxurl,
		data,
		function(response) {
			window.location.reload();
		}
	);
}

function deleteLogLine(id) {
	var data = {
		'action': 'delete_log_line',
		'id': id,
		'nonce': ajax_var.nonce
	};

	jQuery.post(
		ajaxurl,
		data,
		function(response) {
			window.location.reload();
		}
	);
}

function resendMail(id, mailType) {
	var data = {
		'action': 'resend_mail',
		'id': id,
		'mailType': mailType,
		'nonce': ajax_var.nonce
	};

	jQuery.post(
		ajaxurl,
		data,
		function(response) {
			window.location.reload();
		}
	);
}

function getLogPage(id, filter) {
	var data = {
		'action': 'get_log_page',
		'logpageid': id,
		'filter': filter,
		'nonce': ajax_var.nonce
	};

	document.querySelector( '#frm_ovas_connect table.logs' ).innerHTML = '';
	document.querySelector( '#frm_ovas_connect table.logs' ).classList.add( 'loading' );

	jQuery.post(
		ajaxurl,
		data,
		function(response) {
			document.querySelector( '#frm_ovas_connect table.logs' ).closest( '.tab-content' ).innerHTML = response;
			addApiresponseCollapse();
		}
	);
}

function getStaticValuesFromApi(elem, apisource, apifield, subFieldname, linkedToFieldName, linkedToFieldValue, selectedValue, openAfterRefresh = false) {
	var data = {
		'action': 'get_static_values_from_api',
		'apisource': apisource,
		'apifield': apifield,
		'subFieldname': subFieldname,
		'linkedToFieldName': linkedToFieldName,
		'linkedToFieldValue': linkedToFieldValue,
		'nonce': ajax_var.nonce
	};

	elem.closest( 'div.datasourceStaticValueFromApi' ).querySelector( '.select-label' ).classList.add( "loading" );
	elem.closest( 'div.datasourceStaticValueFromApi' ).querySelector( '.select-dropdown' ).classList.add( "loading" );
	jQuery.post(
		ajaxurl,
		data,
		function(response) {
			try {
				let resp = JSON.parse( response );
				if (resp && resp.length > 0) {
					let keyname  = Object.keys( resp[0] )[0];
					let optGroup = null;

					let retArr = apiResponseToArray( resp );

					// Clear dropdown values
					elem.innerHTML = "";

					for (const item of Object.entries( retArr )) {
						if ((typeof item[1] === 'object' && item[1] !== null) && 'children' in item[1]) {
							// Grouped item with parent and children items
							let optGroup = document.createElement( "optgroup" );
							optGroup.setAttribute( "label", item[1]['title'] );
							optGroup.setAttribute( "value", item[0] );
							elem.add( optGroup );

							// Add children
							for (const [key, value] of Object.entries( item[1]['children'] )) {
								optGroup.append( new Option( value, key ) );
							}
						} else {
							// Simple non-grouped list
							elem.append( new Option( item[1], item[0] ) );
						}
					}

					elem.selectedIndex = 0;

					if (selectedValue != null) {
						elem.value = selectedValue;
					}
				}
				// Empty or illegal response
				else {
					let o = new Option( "Invalid or empty API response", "ERR_INVALID_OR_EMPTY_API_REPONSE" );
					o.setAttribute( "disabled", "disabled" );
					elem.classList.add( "error" );
					elem.add( o );
					elem.closest( 'div.datasourceStaticValueFromApi' ).querySelector( '.select-label' ).classList.remove( "loading" );
				}
			} catch (e) {
				elem.closest( 'div.datasourceStaticValueFromApi' ).querySelector( '.select-label' ).classList.remove( "loading" );
			}
			refreshDropdown( elem, openAfterRefresh );
			elem.closest( 'div.datasourceStaticValueFromApi' ).querySelector( '.select-label' ).classList.remove( "loading" );
		}
	);
}

// Grab the API response and create a standardized array with items and subitems
// Since the API return values are non-standardized and can differ depending on which
// function is being called, we can't make any assumptions as to how to put those
// into a dropdown. Thus we need to take the input and standardize it. This way
// the function that creates the dropdown can reliably create the optgroups and options
function apiResponseToArray(resp) {
	return resp.reduce(
		function (accumulator, obj) {
			let itemId         = obj[Object.keys( obj )[0]];
			let parentKeyName  = null;
			let parentKeyNames = ['parentId', 'parentCosttypeId'];

			for (const keyname of parentKeyNames) {
				if (keyname in obj) {
					parentKeyName = keyname;
				}
			}

			if (obj['title'] == null) {
				  obj['title'] = itemId;
			}

			if (parentKeyName in obj) {
				// Object has parents
				if (obj[parentKeyName] && obj[parentKeyName]['value'] == null) {
					// Parent; Generate or fill optgroup
					if ( ! (itemId in accumulator)) {
						accumulator[itemId]             = {};
						accumulator[itemId]['children'] = {};
					}
					accumulator[itemId]['title'] = obj['title'];
				} else {
					if (obj[parentKeyName]['value'] in accumulator) {
						// Group already exists, add it to the group
						accumulator[obj[parentKeyName]['value']]['children'][itemId] = obj['title'];
					} else {
						// Group does not exist, add empty group and add to it
						accumulator[obj[parentKeyName]['value']]                     = {};
						accumulator[obj[parentKeyName]['value']]['title']            = null;
						accumulator[obj[parentKeyName]['value']]['children']         = {};
						accumulator[obj[parentKeyName]['value']]['children'][itemId] = obj['title'];
					}
				}
			} else {
				  // Object is a flat list
				  accumulator[itemId] = obj['title'];
			}

			return accumulator;
		},
		{}
	)
}

document.querySelector( "#frm_ovas_connect" ).addEventListener(
	"submit",
	function(e){
		updateLinks();
	}
);

addApiresponseCollapse();

function addApiresponseCollapse() {
	document.querySelectorAll( ".apicallresponse .status" ).forEach(
		elem =>
		elem.onclick = () => {
			if (elem.querySelector( 'ul' ).style.display == 'block') {
				elem.querySelector( 'ul' ).style.display = 'none';
				elem.querySelector( '.collapseindicator' ).classList.remove( 'dashicons-arrow-down' );
				elem.querySelector( '.collapseindicator' ).classList.add( 'dashicons-arrow-right' );
			} else {
				elem.querySelector( 'ul' ).style.display = 'block';
				elem.querySelector( '.collapseindicator' ).classList.remove( 'dashicons-arrow-right' );
				elem.querySelector( '.collapseindicator' ).classList.add( 'dashicons-arrow-down' );
			}
		}
	);
}

function activateTabs(tab) {
	let parentTab = tab.parentElement.closest( '.tab' );
	if (parentTab) {
		activateTabs( parentTab );
	}

	tab.querySelector( 'label' ).click();
}

function toggleRequest(elem) {
	let overflow        = elem.style.overflow;
	elem.style.overflow = (overflow == 'hidden') ? 'visible' : 'hidden';

	// remove shade if not collapsed
	if (overflow == 'hidden') {
		elem.classList.remove( 'shaded' ); } else {
		elem.classList.add( 'shaded' ); }
}

function optionExists (select, value) {
	const options = Object.values( select.options ).map( option => option.value )
	return options.includes( value )
}

function refreshDropdown(elem, openAfterRefresh) {
	for (const el of selects) {
		if (el.e == elem) {
			// Add element to reloading array to prevent the 'open' event from firing
			// once we open the dropdown, refreshing the contents and ending up in an
			// endless feedback loop.
			reloading.push( el );
			el.reload();
			if (openAfterRefresh) {
				el.open( false );
			}
		}
	}
}

function filterLogs(term) {
	var data = {
		'action': 'filter_logs',
		'filter': term,
		'duration': document.getElementById( 'logtab_filter_limit_override' ).value,
		'nonce': ajax_var.nonce
	};

	document.querySelector( '#frm_ovas_connect table.logs' ).innerHTML = '';
	document.querySelector( '#frm_ovas_connect table.logs' ).classList.add( 'loading' );

	jQuery.post(
		ajaxurl,
		data,
		function(response) {
			document.querySelector( '#frm_ovas_connect table.logs' ).closest( '.tab-content' ).innerHTML = response;
		}
	);
}

// Form validation
jQuery( document ).ready(
	function($){
		// Disable save button while we are getting all data ready to prevent data loss
		// if the user submits the form before everything is ready
		$( '#frm_ovas_connect input[type="submit"]' ).prop( 'disabled', true );

		// Transform select boxes into searchable, multiselectable (only when the select has 'multiple' in the tag) dropdowns
		selects = tail.select(
			"#frm_ovas_connect select",
			{
				search: true,
				descriptions: true,
				hideSelected: false,
				hideDisabled: true,
				multiLimit: 10,
				multiShowCount: false,
				multiContainer: true
			}
		);

		// Restore original selected order from the json to the dropdown labels
		if (selects) {
			for (const select of selects) {
				if (select.e.closest( 'select.datasourceFieldLink' )) {
					let order = select.e.closest( 'select.datasourceFieldLink' ).dataset.order.split( ',' ) ?? null;
					let s     = select.label.querySelectorAll( 'div.select-handle' );

					Array.from( s ).sort(
						function(a, b) {
							return order.indexOf( a.dataset.key ) - order.indexOf( b.dataset.key );
						}
					).forEach(
						el => {
                        select.label.appendChild( el );
						}
					);
				}
			}
		}

		// Restore active tab
		jQuery.post(
			ajaxurl,
			{'action': 'get_active_tab', 'nonce': ajax_var.nonce},
			function(response) {
				activateTabs( document.getElementById( JSON.parse( response ) ).closest( '.tab' ) );
			}
		);

		// Form validation
		$( '#frm_ovas_connect' ).submit(
			function(){
				// return false; // TEMP: prevent submission to make debugging easier
			}
		);

		// Add click event listener to non-disabled 'add field' links
		for (const linky of document.querySelectorAll( '.addFieldLink .popup a' ) ) {
			linky.addEventListener(
				'click',
				function(event) {
					if ( ! linky.classList.contains( 'disabled' )) {
						let parent = event.currentTarget.closest( '.addFieldLink' );
						addFieldLink( parent.dataset.sourcename, parent.dataset.sectionid, parent.dataset.datasourceid, event.currentTarget.dataset.id, event.currentTarget.dataset.hassubfields, linky.dataset.required, event.currentTarget.dataset.hasapicallback, event.currentTarget.dataset.linkedto );
					}
				}
			);
		}

		// Refresh apivalue dropdowns when a user opens it, but only when not already reloading it
		selects.forEach(
			select =>
			select.on(
				"open",
				function() {
					if (reloading.indexOf( select ) == -1) {
						if (select.e.name == 'datasourceStaticValueFromApi') {
							dataSourceChanged( select.e.closest( '.fieldLinkContainer' ).querySelector( '[name="datasourcefields"]' ), true );
						}
					}
					reloading = reloading.filter(
						function(item) {
							return item !== select
						}
					)
				}
			)
		);

		// Track active tab
		$( '.tab-switch' ).change(
			function() {
				var data = {
					'action': 'set_active_tab',
					'tabname': $( this )[0].id,
					'nonce': ajax_var.nonce
				};
				jQuery.post(
					ajaxurl,
					data,
					function(response) {
						// No client-side action needed after post
					}
				);

				// Trigger change event on selected subtab when parent tab is switched
				$( this ).parent().find( '.tabs.subtabs .tab-switch:checked' ).trigger( "change" );
			}
		);

		// Refresh all api value dropdowns on subtab change, to preload them
		$( '.tabs.subtabs .tab-switch' ).change(
			function() {
				let tabSelects = [...$( this )[0].closest( '.tab' ).querySelectorAll( '.fieldLinkContainer [name="datasourcefields"]' )];
				selects.forEach(
					function(select){
						if (tabSelects.includes( select.e )) {
							dataSourceChanged( select.e, false );
						}
					}
				);
			}
		);

		// Hide empty settings page options
		$( '#frm_ovas_connect .settings-general tr' ).has( 'input[type="hidden"]' ).hide();

		// Re-enable the save button once all ajax calls are done
		$( document ).ajaxStop(
			function () {
				$( '#frm_ovas_connect input[type="submit"]' ).prop( 'disabled', false );
			}
		);

		// Prevent 'enter' key from submitting the page if we are in a textarea or
		// the filter box
		$( document ).ready(
			function() {
				$( window ).keydown(
					function(event){
						if ( (event.keyCode == 13 && ! $( document.activeElement ).is( 'textarea' )) ) {
							if (event.target.id == 'ovas_connect_logs_filter') {
								filterLogs( event.target.value );
								event.preventDefault();
								return false;
							}
						}
					}
				);
			}
		);
	}
);
