<html ng-app="app">
<head>
    <title>Raids & Events of Teh Fallen Örder</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" type="text/css" href="https://uicdn.toast.com/tui-calendar/latest/tui-calendar.css"/>
    <link rel="stylesheet" type="text/css" href="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.css"/>
    <link rel="stylesheet" type="text/css" href="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.css"/>
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
            <li><a href="#" ng-click="loadEvents();"><i class="material-icons">refresh</i></a></li>
            <li><a href="#" ng-click="calendar.today();">Today</a></li>
            <li><a href="#" ng-click="calendar.prev();"><i class="material-icons">keyboard_arrow_left</i></a></li>
            <li><a href="#" ng-click="calendar.next();"><i class="material-icons">keyboard_arrow_right</i></a></li>
        </ul>
    </div>
</nav>
<div id="calendar" style="height: 90%;"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.7.9/angular.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>
<script src="https://uicdn.toast.com/tui.code-snippet/v1.5.2/tui-code-snippet.min.js"></script>
<script src="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.min.js"></script>
<script src="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.min.js"></script>
<script src="https://uicdn.toast.com/tui-calendar/latest/tui-calendar.js"></script>
<script>
    var app = angular.module('app', []);
    app.controller('ctrl', function ($scope, $http)
       {
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
                   popupDetailBody: function(schedule) {
                       return (schedule.attendees.length < 6 ? '<span class="green-text"><i class="material-icons tiny">person_add</i> !lfg join ' + schedule.id + '</span>' : '<span class="red-text"><i class="material-icons tiny">people</i> fireteam full</span>');
                   },
               }
           });


           $scope.loadEvents = function ()
           {
               $http.get("<?php print $loadingEndpoint ?? ''; ?>")
                    .then(function (res)
                    {
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
       });
</script>
<?php } ?>
</body>
</html>