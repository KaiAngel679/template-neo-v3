function delay(fn, ms) 
{
    let timer = 0
    return function (...args) 
    {
        clearTimeout(timer)
        timer = setTimeout(fn.bind(this, ...args), ms || 0)
    }
}

$('input[name="_steam_id"]').on("input", (e) => {
    $(".modal_blocks_content > a").remove();
    let modal = $("#open_search");

    if( $(e.currentTarget).val().trim() == "" )
    {
        modal.removeClass("users_found");
        return modal.removeClass("users_notfound")
    }

    modal.addClass("users_notfound");
    $("#search_header").html('<div class="load"><svg><use href="/resources/img/sprite.svg#spinner"></use></svg> '+get_translate_phrase('_lookingPlayer')+' </div>');
})

$('input[name="_steam_id"]').keyup(delay(function (e) {
    searchFromModule(this.value);
}, 500));

function searchFromModule( input = "" )
{
    let modal = $("#open_search");
    modal.addClass("users_notfound");

    $(".modal_blocks_content > a").remove();

    if( input.trim() == "" )
    {
        modal.removeClass("users_found");
        return modal.removeClass("users_notfound")
    }

    (async () => {
        let data = new FormData();
        data.append( "search", input );

        const rawResponse = await fetch(domain+'search/', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: data
        });

        const json = await rawResponse.json();

        if( json.result?.error )
        {
            modal.removeClass("users_found");
            $("#search_header").html(`
                <svg><use href="/resources/img/sprite.svg#x"></use></svg>`+json.result?.error);

            return;
        }

        if( json.result?.players.length && json.result?.players.length > 0 )
        {
            modal.removeClass("users_notfound");
            modal.addClass("users_found");
            $("#search_header").html(`
                <svg><use href="/resources/img/sprite.svg#magnifying-glass"></use></svg>
                `+get_translate_phrase('_Matches_found')+`
                <b>`+json.result?.players.length+`</b>`);

            for( let player of json.result?.players )
            {
                $(".modal_blocks_content").append(`<a href="${domain}profiles/`+player.steam64+`?search=1">
                    <img src="${player.avatar}" alt="">
                    <div class="modal_user_content">
                        <h3>`+player.name+`</h3>
                        <p>`+player.steam64+`</p>
                    </div>
                    <svg><use href="/resources/img/sprite.svg#arrow-top-tight-circle"></use></svg>
                </a>`)
            }
        }
        else
        {
            modal.removeClass("users_found");
            $("#search_header").html(`
            <svg><use href="/resources/img/sprite.svg#x"></use></svg>
            `+get_translate_phrase('_Matches_not_found'));
        }
    })();
}