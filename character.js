    oEditor = new $.fn.dataTable.Editor({
        ajax: "/Private/Characters/getData",
        table: "#datatable",
        formOptions: {
            main: {
                focus: 1      // Initial focus on last_name input field.
            }
        },
        fields: [
            {
                label: "Id:",
                name: "Characters.character_id",
                type: "display"       // requires editor.display.js
            },
            {
                label: "Last name:",
                name: "Characters.last_name"
            },
            {
                label: "Name prefix:",
                name: "Characters.name_prefix",
                labelInfo: "e.g. &quot;The&quot;, &quot;Mr.&quot;"
            },
            {
                label: "First name:",
                name: "Characters.first_name"
            },
            {
                label: "Name suffix:",
                name: "Characters.name_suffix",
                labelInfo: "e.g. &quot;IV&quot;, &quot;Jr.&quot;"
            },
            {
                label: "Stage Show:",
                name:  "StageShows.stageshow_id",
                type: "select",
                placeholder: "Select Stage Show",
                placeholderDisabled: false
            }
        ]
    });

    // $().DataTable() returns a DataTables API instance.
    oTable = $("#datatable").DataTable({
        ajax: {
            url: "/Private/Characters/getData",
            type: "POST"
        },
        responsive: true,
        order: [[0, "desc"]],
        columns: [
            {
                data: "Characters.character_id",
                className: "text-right click-for-child-row"
            },
            {
                data: "Characters.name_prefix",
                className: "text-right"
            },
            {
                data: "Characters.first_name",
                className: "text-right"
            },
            {
                data: "Characters.last_name"
            },
            {
                data: "Characters.name_suffix"
            },
            // Sort column 5 (title_for_display) using data from column 6 (title_body).
            {
                data: "StageShows.title_for_display",
                    orderData: [6],
                    render: function ( data, type, row ) {     
                        return "<a href=\"StageShows?stageshow_id="+row.StageShows.stageshow_id+"\">"+ data +"</a>";
                }
            },
            {
                data: "StageShows.title_body",
                visible: false,
                searchable: false
            },
        ],
        select: {
            style: "os",
            selector: "td:not(.click-for-child-row)"
        }
    });
