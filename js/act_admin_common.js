function warning(message){
    if ( message ){
        jQuery('#warning').text(message);
        jQuery('#submit').prop('disabled', true);
    } else {
        jQuery('#warning').text('');
        jQuery('#submit').prop('disabled', false);
    }
}
function check_email(value){
    let emails = value.split(';');
    for(let email of emails){
        let parts = email.split('@');
        if ( parts.length < 2){
            warning(email + ' invalid email address (@)');
            return false;
        } else {
            let t = parts[1].split('.');
            if ( t.length < 2){
                console.log('email: ' + email + ' parts[1] : ' + parts[1] + ' t.length: ' + t.length);
                warning(email + ' invalid domain (.)');
                return false;
            } else {
                warning(null);
            }
        }
    }
    return true;
}
const areacodes =
{
    "E04003192":{"name":"Ashcombe CP"},
    "E04003193":{"name":"Ashton CP"},
    "E04012121":{"name":"Abbotskerswell CP"},
    "E04003191":{"name":"Ashburton CP"},
    "E04003194":{"name":"Bickington CP"},
    "E04003195":{"name":"Bishopsteignton CP"},
    "E04003196":{"name":"Bovey Tracey CP"},
    "E05011896":{"name":"Bradley"},
    "E04003197":{"name":"Bridford CP"},
    "E04003198":{"name":"Broadhempston CP"},
    "E04003199":{"name":"Buckfastleigh CP"},
    "E05011897":{"name":"Buckland & Milber"},
    "E04003200":{"name":"Buckland in the Moor CP"},
    "E05011898":{"name":"Bushell"},
    "E04003201":{"name":"Christow CP"},
    "E04003202":{"name":"Chudleigh CP"},
    "E04003203":{"name":"Coffinswell CP"},
    "E05011900":{"name":"College"},
    "E04003204":{"name":"Dawlish CP"},
    "E04003235":{"name":"Denbury and Torbryan CP"},
    "E04003205":{"name":"Doddiscombsleigh CP"},
    "E04003206":{"name":"Dunchideock CP"},
    "E04003207":{"name":"Dunsford CP"},
    "E04003208":{"name":"Exminster CP"},
    "E04003209":{"name":"Haccombe with Combe CP"},
    "E04003210":{"name":"Hennock CP"},
    "E04003211":{"name":"Holcombe Burnell CP"},
    "E04003212":{"name":"Ide CP"},
    "E04003213":{"name":"Ideford CP"},
    "E04003214":{"name":"Ilsington CP"},
    "E04003215":{"name":"Ipplepen CP"},
    "E04003216":{"name":"Kenn CP"},
    "E04003217":{"name":"Kenton CP"},
    "E04003218":{"name":"Kingskerswell CP"},
    "E04003219":{"name":"Kingsteignton CP"},
    "E04003220":{"name":"Lustleigh CP"},
    "E04003221":{"name":"Mamhead CP"},
    "E04003222":{"name":"Manaton CP"},
    "E04003223":{"name":"Moretonhampstead CP"},
    "E04003232":{"name":"Tedburn St. Mary CP"},
    "E04003225":{"name":"North Bovey CP"},
    "E04003226":{"name":"Ogwell CP"},
    "E04003227":{"name":"Powderham CP"},
    "E04003228":{"name":"Shaldon CP"},
    "E04003229":{"name":"Shillingford St. George CP"},
    "E04003230":{"name":"Starcross CP"},
    "E04003231":{"name":"Stokeinteignhead CP"},
    "E04003233":{"name":"Teigngrace CP"},
    "E04003234":{"name":"Teignmouth CP"},
    "E04003236":{"name":"Trusham CP"},
    "E04003237":{"name":"Whitestone CP"},
    "E04003238":{"name":"Widecombe in the Moor CP"},
    "E04003239":{"name":"Woodland CP"}
};
function populate_area_select(){
    let body = '';
    for(let code in areacodes){
        body += '<option value="' + code + '" >' + code + ' ' + areacodes[code].name + '</option>';
    }
    jQuery('#areas').html(body);
}