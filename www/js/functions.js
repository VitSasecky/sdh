/**
 * Created by Vit on 20. 2. 2017.
 */
//kryvani sekci


function deleteItem(message, link) {
    if(confirm(message)){
        location.href = link;
    }
}

function showSDHnews() {
    $(".SDH").show();
    $(".RSS").hide();
}

function showRSSchannels() {
    $(".RSS").show();
    $(".SDH").hide();

}

$(document).ready(function () {
    showSDHnews();
    $('#SDH').click(function () {
        showSDHnews();
    });

    $('#RSS').click(function () {
        showRSSchannels();
    })
})
;



//facebook
(function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s);
    js.id = id;
    js.src = "//connect.facebook.net/cs_CZ/sdk.js#xfbml=1&version=v2.7";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

