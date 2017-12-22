function expandCollapse(torrentId)
{
    var plusMinusImg = document.getElementById("plusminus"+torrentId);
    var detailRow = document.getElementById("details"+torrentId);

    if (plusMinusImg.src.indexOf("pic/plus.gif") >= 0) {
        plusMinusImg.src = "pic/minus.gif";
        detailRow.style.display = "block";
    } else {
        plusMinusImg.src = "pic/plus.gif";
        detailRow.style.display = "none";
    }
}