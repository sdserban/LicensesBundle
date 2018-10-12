<html>
<head>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
</head>

<body>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="form_main">
                <h4 class="heading"><strong>License management</strong></h4>
                <div class="form">
                    <form action="/licenses/checkit" method="post" id="licMngFrm" name="licMngFrm" class="form-horizontal">
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="licenseId">License ID:</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" name="licenseId" id="licenseId" value="<?php echo $this->licenseId; ?>" placeholder="Please enter license ID if you have one">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="licenseType">License type:</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="licenseType" value="<?php echo $this->licenseType; ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="licenseClient">Client:</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="licenseClient" value="<?php echo $this->licenseClient; ?>" readonly >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="licensedModules">Licensed Modules:</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="licensedModules" value="<?php echo $this->licensedModules; ?>" readonly >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="statusMessage">Status:</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="statusMessage" value="<?php echo "$this->status / $this->statusMessage"; ?>" readonly >
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">
                            </div>
                            <div class="col-sm-9 text-left">
                                <input type="submit" value="close" name="button" class="btn btn-primary active">
                                <input type="submit" value="save & check" name="button" class="btn btn-primary active">
                                <input type="submit" value="reset" name="button" class="btn btn-primary <?php echo ($this->licenseId == "" ? "disabled": "active") ?>">
                                <input type="submit" value="get demo license" name="button" class="btn btn-primary <?php echo ($this->licenseId == "" ? "active": "disabled") ?>">
                            </div>
                            <div class="col-sm-1 text-right">
                                <input type="submit" value="delete" name="button" class="btn btn-danger <?php echo ((($this->licenseId == "") || ($this->licenseType == "demo")) ? "disabled": "active") ?>">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div
    </div>
</div>
</body>
</html>
