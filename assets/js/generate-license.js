class GenerateLicense {
    constructor(){
        this.__getLicenseInfo();
    }

    __getLicenseInfo(){
        const downloadLicenseBtn = document.querySelectorAll('.download-product-license');
        downloadLicenseBtn.forEach((element)=>{
          element.addEventListener('click', (e)=>{
              e.preventDefault();
              let orderID= element.getAttribute('data-order-id');
              let userID= element.getAttribute('data-user-id');
              jQuery.ajax({
                  type: "get",
                  url: ajaxurl,
                  data: {
                      action: "generate_xml_on_request",
                      order_id : orderID,
                      user_id : userID,
                  },
                  success: function success(response) {
                      var a = window.document.createElement('a');
                      a.href = window.URL.createObjectURL(new Blob([JSON.parse(response,null,2)], {type: 'application/xml'}));
                      a.download = 'license.xml';

// Append anchor to body.
                      document.body.appendChild(a);
                      a.click();

// Remove anchor from body
                      document.body.removeChild(a);
                  }
              });

          });
        });
    }
}
window.addEventListener("DOMContentLoaded", function() {
    new GenerateLicense();
}, false);
