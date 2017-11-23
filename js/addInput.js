/* http://www.randomsnippets.com/2008/02/21/how-to-dynamically-add-form-elements-via-javascript/
 * Allen Liu
 */
var counter = 1;
var limit = 20;
function addInput(divName){
     if (counter == limit)  {
          alert("Du hast das Limit von " + counter + " Antworten erreicht.");
     }
     else {
          var newdiv = document.createElement('div');
          newdiv.innerHTML = "Entry " + (counter + 1) + " <br><input type='text' name='myInputs[]'>";
          document.getElementById(divName).appendChild(newdiv);
          counter++;
     }
}