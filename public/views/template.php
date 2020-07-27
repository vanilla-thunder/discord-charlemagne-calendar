<html ng-app="app">
<head>
    <title>Raids & Events of Teh Fallen Örder</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.25, maximum-scale=2.0"/>
    <link rel="stylesheet" href="src/materialize.min.css"/>
    <link rel="stylesheet" type="text/css" href="src/tui-calendar.css"/>
    <link rel="stylesheet" type="text/css" href="src/tui-date-picker.css"/>
    <link rel="stylesheet" type="text/css" href="src/tui-time-picker.css"/>
    <style>
    /* fallback */
@font-face {
  font-family: 'Material Icons';
  font-style: normal;
  font-weight: 400;
  src: url(src/flUhRq6tzZclQEJ-Vdg-IuiaDsNc.woff2) format('woff2');
}

.material-icons {
  font-family: 'Material Icons';
  font-weight: normal;
  font-style: normal;
  font-size: 24px;
  line-height: 1;
  letter-spacing: normal;
  text-transform: none;
  display: inline-block;
  white-space: nowrap;
  word-wrap: normal;
  direction: ltr;
  -moz-font-feature-settings: 'liga';
  -moz-osx-font-smoothing: grayscale;
}

    	body { zoom: 150%; }
    	.spin {
    		animation-name: spin;
  			animation-duration: 500ms;
  			animation-iteration-count: 1;
  			animation-timing-function: linear;
    	}
    	@-moz-keyframes spin {
    	from { -moz-transform: rotate(0deg); }
    		to { -moz-transform: rotate(360deg); }
		}
		@-webkit-keyframes spin {
		    from { -webkit-transform: rotate(0deg); }
	    	to { -webkit-transform: rotate(360deg); }
		}
		@keyframes spin {
	    	from {transform:rotate(0deg);}
    		to {transform:rotate(360deg);}
		}
    </style>
</head>
<body ng-controller="ctrl">
<?php if(!$auth) {	?>
<div class="container">
	<form action="/login" method="post" class="row" autocomplete="off">
        <div class="input-field col s8">
        	<input name="pass" autocomplete="false" id="pass" type="password" placeholder="Password" autoc>
        </div>
        <div class="input-field col s4">
        	<input type="submit" class="col s12 waves-effect waves-light btn" value="login"/>
        </div>
	</form>
	<?php if (isset($error)) { print '<div class="row"><div class="card-panel red lighten-1 whitte-text">'.$error.'</div>'; } ?>
</div>
<?php } ?>
<?php if($auth) { ?>
<nav>
    <div class="nav-wrapper">
        <a href="#" class="brand-logo">&nbsp;&nbsp;Raids & Events</a>
        <ul id="nav-mobile" class="right">
            <li><a href="#" ng-click="loadEvents();"><i id="refresh" class="material-icons" ng-class="{'spin':progress>0}">refresh</i></a></li>
            <li><a href="#" ng-click="calendar.today();">Today</a></li>
            <li><a href="#" ng-click="calendar.prev();"><i class="material-icons">keyboard_arrow_left</i></a></li>
            <li><a href="#" ng-click="calendar.next();"><i class="material-icons">keyboard_arrow_right</i></a></li>
        </ul>
    </div>
</nav>
<div id="calendar" style="height: 90%;"></div>

<script src="src/materialize.min.js"></script>
<script src="src/angular.min.js"></script>
<script src="src/moment.min.js"></script>
<script src="src/tui-code-snippet.min.js"></script>
<script src="src/tui-time-picker.min.js"></script>
<script src="src/tui-date-picker.min.js"></script>
<script src="src/tui-calendar.js"></script>
<script>
    var app = angular.module('app', []);
    app.controller('ctrl', function ($scope, $http, $timeout, $interval)
    {
        $scope.progress = 0;
        $scope.calendar = new tui.Calendar('#calendar', {
            isReadOnly: true,
            defaultView: 'month',
            taskView: false,
            scheduleView: ['time'],
            useDetailPopup: true,
            month: {
                startDayOfWeek: 1,
                visibleWeeksCount: 3
            },
            template: {
                time: function (schedule)
                {
                    console.log(schedule);
                    return '<strong>' + moment(schedule.start.getTime()).format('HH:mm') + '</strong> ' + schedule.title;
                },
                monthGridHeader: function (dayModel)
                {
                    var date = parseInt(dayModel.date.split('-')[2], 10);
                    var month = moment(dayModel.date).startOf("month").format('MMMM');
                    var classNames = ['tui-full-calendar-weekday-grid-date '];

                    if (dayModel.isToday)
                    {
                        classNames.push('green white-text');
                    }

                    return '<span class="' + classNames.join(' ') + '" style="width:auto;">&nbsp;' + date + ' ' + month + '&nbsp;</span>';
                },
                popupDetailBody: function (schedule)
                {
                    var html = ''; //(schedule.body !== schedule.title ? '<div>'+schedule.body+'</div>' : '');
                    // alternative joins
                    if(typeof schedule.raw !== "undefined" && schedule.raw && typeof schedule.raw.alternatives !== "undefined" && schedule.raw.alternatives.length > 0) html += '<span class=""><i class="material-icons tiny">perm_identity</i> Alternatives: ' + schedule.raw.alternatives.join(', ') + '</span><br/>';
                    // join id
                    html += '<span class="green-text"><i class="material-icons tiny">person_add</i> !lfg join ' + schedule.id + '</span><br/>';
                    // 6 people joined? might be full
                    if (schedule.attendees.length > 5) html += '<span class="red-text"><i class="material-icons tiny">people</i> fireteam might be full</span>';
                    return html;
                },
                popupDetailLocation: function (schedule) { return schedule.body; }
            }
        });

        $scope.loadEvents = function ()
        {
            $scope.progress++;
            $timeout(function () { $scope.progress--;}, 500);
            $http.get("<?php print $loadingEndpoint ?? ''; ?>")
                 .then(function (res)
                 {
                     //$.timeout(function(){ $scope.progress--;}, 500);


                     if (res.status == 200)
                     {
                         console.log("loaded events", res.data);
                         $scope.calendar.clear();
                         $scope.calendar.createSchedules(res.data, true);
                         $scope.calendar.render();
                     }
                     else console.log("dat war nix", res);

                 });
        };
        $scope.loadEvents();

        $scope.keepAlive = $interval(function() {
            $scope.loadEvents();
        }, 180000);
    });
</script>
<?php } ?>
</body>
</html>