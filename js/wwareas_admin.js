
const columnspecs = {
    name: {
        header: 'Name',
        type: 'literal',
        width: '100px',
        size: 30,

    },
    wardens: {
        header: 'No. Wardens',
        type: 'text',
        checkNumber:true,
        width: '50px',
        size: 10
    },
    text: {
        header: 'Parish Text',
        type:'textarea',
        width: '500px',
        preventKey: function(key){
            return key === '"';
        },
        cols: 40,
        rows: 10
    }
};
const options = {norowid: true};
let listid;
let table = null;
let parsed_data = null;
async function pageload() {
    jQuery(document).ready(function($) { 
        $(document).ready(async function() {
            populate_area_select()
            const container = $("#act-admin-list-container");
            listid = container.attr('list-id');
            console.log('listid: ' + listid);
            try {
                parsed_data = await getjson(listid, columnspecs);
                //if ( parsed_data == null){
                //    console.error("Error fetching list data:", data.message);
                //    alert(data.message);
                //}
            } catch (error) {
                console.error("Error fetching list data:", error);
                alert("An error occurred while fetching the list data.");
            }
        });
    });
}
let areadata = null;
let current_code = null;
function showarea(code){
    console.log('showarea: ' + code);
    if ( parsed_data ){
        current_code = code;
        areadata = parsed_data[code];
        console.log('area data: ' + JSON.stringify(areadata));
        if ( areadata ){
            table = createNameValueEditor('datatable', areadata, code, columnspecs, 
                function(tableobj){
                    parsed_data[code] = tableobj;
                },
                options);
        }
    }
}
function on_area(){
    // get selected area
    let code = jQuery('#areas').val();
    if ( showarea ){
        showarea(code);
    }
}
// TODO on_submit
// check all emails are valid
async function on_submit(){
    try {
        warning('Saving ' + listid + '.json ...');
        console.log('Saving ' + listid + ' : ' + current_code);
        if ( current_code ){
            console.log('parsed_data[' + current_code + ']: ' + JSON.stringify(parsed_data[current_code]));
            if ( parsed_data[current_code]){
                let result = await savejson(listid, parsed_data);
                console.log(JSON.stringify(result));
                if ( !result.success ){
                    warning('Error(s) occurred - details: console (developer tools)');
                } else {
                    warning(null);
                }
            } else {
                warning("response not saved, system error: parsed_data was null");
                throw new Error('parsed_data[' + current_code +'] was null');
            }
        } else {
            warning('Saving when current_code null.')
        }
    } catch(error){
        console.error("Error saving list data:", error);
        alert("An error occurred while saving the list data.");  
    }
}
