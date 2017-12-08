function expandCollapse(newsId)
{
    var plusMinusImg = document.getElementById("plusminus"+newsId);
    var detailRow = document.getElementById("details"+newsId);

    if (detailRow.style.display == "none") {
        plusMinusImg.src = "pic/minus.gif";
        detailRow.style.display = "table-row";
    } else {
        plusMinusImg.src = "pic/plus.gif";
        detailRow.style.display = "none";
    }
}