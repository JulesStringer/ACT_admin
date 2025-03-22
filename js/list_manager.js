
async function getjson(listid){
    const ajaxurl = my_ajax_object.ajaxurl; // Get ajaxurl from localized data

    try {
        const response = await fetch(ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8", // Important!
            },
            body: new URLSearchParams({
                'action': 'get_act_admin_list_data', // Correct action name
                'list_id': listid
            })
        });
console.log('Submitted fetch');
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP error ${response.status}: ${errorText}`);
        }
console.log('Response OK');
        const json = await response.json(); // or response.json() if you are sending json from php
console.log('Got json');
        const jsondata = JSON.parse(json.data);
console.log('got jsondata - type: ' + typeof(jsondata));
        if (jsondata) {
            return jsondata
        } else {
            throw new Error("Error fetching list data. No data received."); // More descriptive message
        }
    } catch (error) {
        console.error("Error in getlist:", error); // Log the full error object for debugging
        throw error; // Re-throw the error so the calling function knows about it
    }

}
async function getcsv(listid, columnspecs) { // Removed dataurl parameter
    const ajaxurl = my_ajax_object.ajaxurl; // Get ajaxurl from localized data

    try {
        const response = await fetch(ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8", // Important!
            },
            body: new URLSearchParams({
                'action': 'get_act_admin_list_data', // Correct action name
                'list_id': listid
            })
        });
console.log('Submitted fetch');
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP error ${response.status}: ${errorText}`);
        }
console.log('Response OK');
        const json = await response.json(); // or response.json() if you are sending json from php
console.log('Got json');
        const csvdata = json.data;
console.log('got csvdata - type: ' + typeof(csvdata));
        if ( typeof (csvdata) === 'object'){
            console.log('keys of csvdata: ' + Object.keys(csvdata));
            if ( csvdata.message) {
                console.log('message: ' + csvdata.message);
            }
        }
        if (csvdata) {
            return parsecsv(csvdata, columnspecs);
        } else {
            throw new Error("Error fetching list data. No data received."); // More descriptive message
        }

    } catch (error) {
        console.error("Error in getcsv:", error); // Log the full error object for debugging
        throw error; // Re-throw the error so the calling function knows about it
    }
}
function parsecsv(csvdata, columnspecs) {  // Function name lowercase
    const lines = csvdata.split('\n'); // Variable name lowercase
    const headers = lines[0].split(',');
    const data = {};
    let colids = Object.keys(columnspecs);
    for (let i = 1; i < lines.length; i++) {
        let line = lines[i];
        const values = [];
        let field = '';
        for(j=0; j < line.length; j++){
            if ( line[j] === '"' && field.length == 0){
                j++;
                while(line[j] !== '"' && j < line.length){
                    field += line[j];
                    j++;
                }
            } else if ( line[j] === ',' ){
                values.push(field);
                field = '';
            } else if ( line[j] >= ' ' ){
                field += line[j];
            }
        }
        if ( field.length > 0){
            values.push(field);
        }
        if ( values.length > 0){
            let ok = false;
            for(let value of values){
                if ( value.length > 0){
                    ok = true;
                }
            }
            if ( ok ){
                const row = {};
                for (let j = 0; j < colids.length; j++) {
                    const colid = colids[j]; // Variable name lowercase
                    let columnspec = columnspecs[colid];
                    row[colid] = values[j];
                    if ( columnspec.isNumber){
                        row[colid] = parseFloat(values[j]);
                    }
                }
                data[i] = row;
            }
        }
    }
    return data;
}
function formcsv(data, columnspecs){
    let csv = '';
    let colids = Object.keys(columnspecs);
    for(let colid of colids){
        let columnspec = columnspecs[colid];
        if ( csv.length > 0){
            csv += ',';
        }
        csv += '"' + columnspec.header + '"';
    }
    csv += '\r\n';
    let ct = 0;
    for(let rowid in data){
        let row = data[rowid];
        for(let colid of colids){
            if ( ct > 0 ){
                csv += ',';
            }
            if ( columnspecs[colid].isNumber ){
                let value = row[colid].toString();
                //value = value.replace(/"/g, '""');
                csv += '"' + value + '"';
            } else {
                csv += '"' + row[colid] + '"';
            }
            ct++;
        }
        csv += '\r\n';
        ct = 0;
    }
    return csv;
}
async function savecsv(listid, columnspecs, data) {  // Removed dataurl
    const ajaxurl = my_ajax_object.ajaxurl; // Get ajaxurl from localized data
console.log('data: ' + JSON.stringify(data));
    let csv = formcsv(data, columnspecs);
console.log('csv:' + csv);
    try {
        const blob = new Blob([csv], { type: 'text/csv' }); // Create a Blob from the CSV string

        const formdata = new FormData();
        formdata.append('action', 'save_act_admin_list_data');
        formdata.append('list_id', listid);
        formdata.append('data', blob, listid + '.csv'); // Append the raw CSV string
console.log("Formdata list_id:", formdata.get('list_id')); // Check list_id
console.log("Formdata data:", formdata.get('data'));      // Check data
        const response = await fetch(ajaxurl, {
            method: 'POST',
            body: formdata
        });

        if (!response.ok) {
            const errorText = await response.text(); // Get error message from server
            throw new Error(`HTTP error! status: ${response.status}: ${errorText}`); // Include server error
        }

        const result = await response.json(); // Parse JSON response from PHP

        if (result.success) { // Check for success or error
            console.log("CSV saved successfully:", result.message);
            return result; // Return the result object for further processing if needed.
        } else {
            console.error("Error saving CSV:", result.message);
            throw new Error(result.message); // Throw error with message from server
        }

    } catch (error) {
        console.error("Error in savecsv:", error);
        throw error; // Re-throw the error
    }
}
async function savejson(listid, data) {  // Removed dataurl
    const ajaxurl = my_ajax_object.ajaxurl; // Get ajaxurl from localized data
console.log('data: ' + JSON.stringify(data));
    try {
        const blob = new Blob([JSON.stringify(data)], { type: 'application/json' }); // Create a Blob from the CSV string

        const formdata = new FormData();
        formdata.append('action', 'save_act_admin_list_data');
        formdata.append('list_id', listid);
        formdata.append('data', blob, listid + '.json'); // Append the raw string
console.log("Formdata list_id:", formdata.get('list_id')); // Check list_id
console.log("Formdata data:", formdata.get('data'));      // Check data
        const response = await fetch(ajaxurl, {
            method: 'POST',
            body: formdata
        });

        const result = await response.json(); // Parse JSON response from PHP
        if (!response.ok) {
            console.error("Server Error:", JSON.stringify(result)); // Log the full response data
            return { success: false, result: result }; // Spread the response data into the error object
        }


        if (result.success) { // Check for success or error
            console.log("JSON saved successfully:", result.message);
            return result; // Return the result object for further processing if needed.
        } else {
            console.error("Error saving JSON:", JSON.stringify(result));
            return { success: false, result: result }; // Return error object
        }

    } catch (error) {
        console.error("Error in savejson:", error);
        return { success: false, message: error.message }; // Return error object
    }
}
