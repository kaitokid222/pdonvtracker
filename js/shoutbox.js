var php_file = "shoutbox.php";

function get_GET_params()
{
    var GET = new Array();
    if(location.search.length > 0)
    {
        var get_param_str = location.search.substring(1, location.search.length);
        var get_params = get_param_str.split("&");
        for(i = 0; i < get_params.length; i++)
        {
            var key_value = get_params[i].split("=");
            if(key_value.length == 2)
            {
                var key = key_value[0];
                var value = key_value[1];
                GET[key] = value;
            }
        }
    }
    return(GET);
}

function get_GET_param(key)
{
    var get_params = get_GET_params();

    if(get_params[key])
    {
        return(get_params[key]);
    }
    else
    {
        return false;
    }
}

function update()
{
    $.post(php_file, {}, function(data){ $("#screen").html(data);});

    setTimeout('update()', 5000);
}

function update_history()
{
    $.post(php_file + "?history=1", {}, function(data){ $("#screen_history").html(data);});

    setTimeout('update_history()', 10000);
}

function send_msg()
{
    $.post(php_file, {
                         message: $("#message").val()
                     },
                     function(data)
                     {
                         $("#screen").html(data);
                         $("#message").val("");
                     }
           );
}

$(document).ready(
  function()
  {
      if (get_GET_param("history") == "1") update_history();
      else update();

      $("#button").click(function()
                         {
                            send_msg();
                         }
                        );

      $('#message').keyup(function(e)
                          {
                              if(e.keyCode == 13)
                              {
                                    send_msg();
                              }
                          }
                         );
  }
);


function modifypost(option, nachricht, history)
{
    var ajax = new tbdev_ajax();
    var varsString = "";

    ajax.onShow ('');
    ajax.requestFile = php_file;
    ajax.setVar("ajax", "yes");

    if (option == "hid")  ajax.setVar("hid", nachricht);
    if (option == "del")  ajax.setVar("del", nachricht);
    if (option == "view") ajax.setVar("view", nachricht);

    if (history == "0")
    {
        ajax.method = 'POST';
    }
    else
    {
       ajax.setVar("history", "1");
       ajax.method = 'GET';
    }

    ajax.element = 'ajax';
    ajax.sendAJAX(varsString);

    document.getElementById("popup").style.display = "none";
}

//function popup_sb(menu_id, user_id, hide, sichtbar, del, history, ban, priv, can_priv, usr)
function popup_sb(menu_id, user_id, hide, sichtbar, del, history, usr)
{
    var menu   = document.getElementById("popup");
    var sender = document.getElementById("menu_" + menu_id);

    menu.innerHTML = "";

    if (menu.style.display == "none")
    {
        if (hide == "1")
        {
            var a   = document.createElement("a");

            if (sichtbar == "yes")
            {
                var txt = document.createTextNode("Post ausblenden");

                a.setAttribute("href", "javascript:void(0);");
                a.setAttribute("onClick", "modifypost('hid','" + menu_id + "','" + history + "');");
                a.setAttribute("id", "hide" + menu_id);
            }
            else
            {
                var txt = document.createTextNode("Post einblenden");

                a.setAttribute("href", "javascript:void(0);");
                a.setAttribute("onClick", "modifypost('view','" + menu_id + "','" + history + "');");
                a.setAttribute("id", "hide" + menu_id);
            }
            a.appendChild(txt);
            menu.appendChild(a);

            document.getElementById("hide" + menu_id).className = "sb_button";
        }

        if (del == "1")
        {
            var a   = document.createElement("a");
            var txt = document.createTextNode("Post löschen");

            a.setAttribute("href", "javascript:void(0);");
            a.setAttribute("onClick", "modifypost('del','" + menu_id + "','" + history + "');");
            a.setAttribute("id", "del" + menu_id);

            a.appendChild(txt);
            menu.appendChild(a);

            document.getElementById("del" + menu_id).className = "sb_button";
        }

        if (usr == "1")
        {
            var a   = document.createElement("a");
            var txt = document.createTextNode("zum Benutzerprofil");

            a.setAttribute("href", "userdetails.php?id=" + user_id);
            a.setAttribute("id", "profil" + menu_id);

            a.appendChild(txt);
            menu.appendChild(a);

            document.getElementById("profil" + menu_id).className = "sb_button";
        }

        var pos = getMenuPosition(sender);

        menu.className = "tablea";

        menu.style.left     = (pos.x + 2) + "px";
        menu.style.top      = (pos.y + 11) + "px";
        menu.style.border   = "solid #000000 1px";
        menu.style.padding  = "3px";
        menu.style.position = "absolute";
        menu.style.display  = "inline";
    }
    else
    {
        menu.style.display = "none";
    }
}

function getMenuPosition(sender)
{
    var pos     = {y:0, x:0};
    var element = sender;

    if (document.getElementById("screen") != null)
    {
        div_top = document.getElementById("screen").scrollTop;
    }
    else
    {
        div_top = document.getElementById("screen_history").scrollTop;
    }

    if(element)
    {
        var elem = element;

        while(elem && elem.tagName.toUpperCase() != 'BODY')
        {
            pos.y += elem.offsetTop;
            pos.x += elem.offsetLeft;

            elem = elem.offsetParent;
        }
    }
    pos.y -= div_top;  // Scroll-Top am Ende abziehen

    return pos;
}

function em(theSmilie)
{
    doAddTags(theSmilie, '', 'message');
}

function winop()
{
    windop = window.open("smilies.php", "mywin", "");
}