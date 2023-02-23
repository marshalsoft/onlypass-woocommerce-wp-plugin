
let makeCode = (e) => {
    for (var a = "", t = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789", i = t.length, r = 0; r < e; r++) a += t.charAt(Math.floor(Math.random() * i));
    return a;
};
var loaderIframe = document.createElement("iframe");
loaderIframe.style ="z-index: 2147483647; background: rgba(0, 0, 0, 0.46); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 0px none transparent; overflow: hidden; margin: 0px; padding: 0px; -webkit-tap-highlight-color: transparent; position: fixed; left: 0px; top: 0px; width: 100%; visibility: hidden; height: 100%; display:none;";
loaderIframe.setAttribute("id", "bani-loader-frame" + makeCode(6));
var iframe = document.createElement("iframe");
function hideLoaderIframe() {
    loaderIframe.style =
        "z-index: 2147483647; background: transparent; border: 0px none transparent; overflow: hidden; margin: 0px; padding: 0px; -webkit-tap-highlight-color: transparent; position: fixed; left: 0px; top: 0px; width: 100%; visibility: hidden; height: 100%; display:none;";
}
function showLoaderIframe() {
    loaderIframe.style =
        "z-index: 2147483647; background: rgba(0, 0, 0, 0.46); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 0px none transparent; overflow: hidden; margin: 0px; padding: 0px; -webkit-tap-highlight-color: transparent; position: fixed; left: 0px; top: 0px; width: 100%; visibility: visible; height: 100%; display:block;";
}

function hideIframe() {
    iframe.style =
        "z-index: 2147483647; background: transparent; border: 0px none transparent; overflow: hidden; margin: 0px; padding: 0px; -webkit-tap-highlight-color: transparent; position: fixed; left: 0px; top: 0px; width: 100%; visibility: hidden; height: 100%; display:none;";
}
function showIframe() {
    iframe.style =
        "z-index: 2147483647; background: transparent; border: 0px none transparent; overflow: hidden; margin: 0px; padding: 0px; -webkit-tap-highlight-color: transparent; position: fixed; left: 0px; top: 0px; width: 100%; visibility: visible; height: 100%; display:block;";
}
let Close;
let onCallback;
var ifrm;
document.addEventListener('DOMContentLoaded', function () {
    document.body.appendChild(loaderIframe);
    iframe.style = "z-index: 2147483647; background: transparent; border: 0px none transparent; overflow: hidden; margin: 0px; padding: 0px; -webkit-tap-highlight-color: transparent; position: fixed; left: 0px; top: 0px; width: 100%; visibility: hidden; height: 100%; display:none;";
    iframe.src = "https://stage-checkout.getbani.com/";
    iframe.allowPaymentRequest = !0;
    let baniFrameId = "bani-frame" + makeCode(6);
    iframe.setAttribute("id", baniFrameId);
    document.body.appendChild(iframe);
    ifrm = document.getElementById(baniFrameId).contentWindow;
    // console.log("append:",ifrm);
})
    window.BaniPopUp = async (e) => {
        let a = {...e};
        // console.log("Bani:",e);
        Close = e?.onClose;
        onCallback = e?.callback;
           let t = {
               amount: e?.amount,
               phoneNumber:e?.phoneNumber,
               email: e?.email,
               firstName: e?.firstName,
               lastName: e?.lastName,
               merchantKey: e?.merchantKey,
               metadata: e?.metadata,
               ref: e?.ref,
               orderRef: e?.orderRef,
               merchantRef: e?.merchantRef,
           };
        // console.log("Bani:",t);
           a = JSON.stringify(a);
           showLoaderIframe();
           window.parent.postMessage({
               ...t,
               checkoutReady: !0
           }, "*");
           ifrm.postMessage({...t, checkoutReady: !0}, "*");
    };

const handleMessages = (e) => {
    let a = e?.data?.type || e?.data,
        t = e?.data?.response;
    switch (a) {
        case "CHECKOUTREADY":
            showIframe();
            break;
        case "CHECKOUTCLOSE":
            hideIframe(), hideLoaderIframe();
            break;
        case "ONCLOSE":
            // onClose && onClose({ ...t });
            break;
        case "CALLBACK":
            onCallback && onCallback({ ...t });
    }
};
async function listenForMessages() {
    window.addEventListener("message", handleMessages);
}
listenForMessages();


