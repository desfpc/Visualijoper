document.addEventListener('DOMContentLoaded', function(){

    function visualijoperGetParents(e) {
        let result = [];
        for (let p = e && e.parentElement; p; p = p.parentElement) {
            result.push(p);
        }
        return result;
    }

    let visualijoperElements = document.querySelectorAll(".vj-header_clickable, .visualijoper__row_clickable .vj-row__header");
    for (let i = 0; i < visualijoperElements.length; i++) {
        visualijoperElements[i].onclick = function(e){
            let parents = visualijoperGetParents(e.currentTarget);
            let body = [...parents[0].children].filter(n => n.classList.contains('vj-body'));
            if (body[0].classList.contains('active')) {
                body[0].classList.remove('active');
            } else {
                body[0].classList.add('active');
            }
        };
    };
});