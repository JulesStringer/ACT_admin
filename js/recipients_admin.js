
const columnspecs = {
    name: {
        header: 'Name',
        type: 'text',
        width: '200px',
        size: 30,
        preventKey: function(key){
            return key === '"';
        }
    },
    email: {
        header: 'Email',
        type: 'text',
        width: '500px',
        size: 60,
        preventKey: function(key){
            return key === '"' || key === ',';
        },
        onchange: function(value, table, rowobj, rowid){
            check_email(value);
        }
    }
};
const options = {norowid: true};
let listid;
let table = null;
async function pageload() {
    jQuery(document).ready(function($) { 
        $(document).ready(async function() {
            const container = $("#act-admin-list-container");
            listid = container.attr('list-id');
            console.log('listid: ' + listid);
            try {
                let parsed_data = await getcsv(listid, columnspecs);
                if ( parsed_data){
                    table = createTableEditor('datatable', parsed_data, columnspecs, 
                        function(tableobj){
                //            data = tableobj; // no point as these are already the samee thing
                            /// TODO savedata
                        },
                        options, true);
//                } else {
//                    console.error("Error fetching list data:", data.message);
//                    alert(data.message);
                }
            } catch (error) {
                console.error("Error fetching list data:", error);
                alert("An error occurred while fetching the list data.");
            }
        });
    });
}
function validate(rows){
    for(let rowid in rows){
        let row = rows[rowid];
        if ( check_email(row.email) === false ){
            return false;
        }
    }
    return true;
}
// TODO on_submit
// check all emails are valid
async function on_submit(){
    try {
        if ( validate(table.tableobj)){
            console.log('Saving ' + listid);
            warning('Saving ' + listid + '.csv ...');
            await savecsv(listid, columnspecs, table.tableobj);
            warning(null);
        }
    } catch(error){
        console.error("Error saving list data:", error);
        alert("An error occurred while saving the list data.");  
    }
}
