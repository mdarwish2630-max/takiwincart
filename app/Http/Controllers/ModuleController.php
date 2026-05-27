<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Facades\ModuleFacade as Module;
use Illuminate\Support\Facades\Artisan;
use App\Models\AddOnManager;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use ZipArchive;
use App\Models\Addon;
use App\Models\Utility;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(auth()->user() && auth()->user()->isAbleTo('Manage Module'))
        {
            $modules = Module::allModules();
            //$modules = [];
            $module_path = Module::getPath();
            //$module_path = null;
            $addon_themes = Addon::get();
            $theme = Utility::BuyMoreTheme();
            
            $addon_tab = session()->get('addon_tab');
            if (empty($addon_tab)) {
                $addon_tab = 'pills-premium-tab';
            }
            return view('module.index',compact('modules','module_path', 'theme', 'addon_themes', 'addon_tab'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function enable(Request $request)
    {
        $module = Module::find($request->name);
        if (!empty($module)) {
            // Sidebar Performance Changes
            sideMenuCacheForget('all');
            if ($module->isEnabled()) {
                $module = Module::find($request->name);
                $check_child_module = $this->Check_Child_Module($module);
                if ($check_child_module == true) {
                    $module->disable();
                    Module::moduleCacheForget($request->name);
                    return redirect()->back()->with('success', __('Module Disable Successfully!'));
                } else {
                    $addon = AddOnManager::where('module', $request->name)->first();
    
                    if (empty($addon)) {
                        Artisan::call('migrate --path=/packages/workdo/' . $request->name . '/src/database/migrations');
                        Artisan::call('package:seed ' . $request->name);
    
                        $filePath = base_path('packages/workdo/' . $request->name . '/module.json');
                        $jsonContent = file_get_contents($filePath);
                        $data = json_decode($jsonContent, true);
    
    
                        $addon = new AddOnManager;
                        $addon->module = $data['name'];
                        $addon->name = $data['alias'];
                        $addon->monthly_price = $data['monthly_price'] ?? 0;
                        $addon->yearly_price = $data['monthly_price'] ?? 0;
                        $addon->package_name = $data['package_name'];
                        $addon->save();
                        Module::moduleCacheForget($request->name);
                    }
                    $module = Module::find($request->name);
                    $check_parent_module = $this->Check_Parent_Module($module);
                    if ($check_parent_module['status'] == true) {
                        Artisan::call('migrate --path=/packages/workdo/' . $request->name . '/src/database/migrations');
                        Artisan::call('package:seed ' . $request->name);
                        $module->enable();
                        return redirect()->back()->with('success', __('Module Enable Successfully!'));
                    } else {
                        return redirect()->back()->with('error', __($check_parent_module['msg']));
                    }
                }
            } else {
                $addon = AddOnManager::where('module', $request->name)->first();
                if (empty($addon)) {
                    Artisan::call('migrate --path=/packages/workdo/' . $request->name . '/src/database/migrations');
                    Artisan::call('package:seed ' . $request->name);

                    $filePath = base_path('packages/workdo/' . $request->name . '/module.json');
                    $jsonContent = file_get_contents($filePath);
                    $data = json_decode($jsonContent, true);


                    $addon = new AddOnManager;
                    $addon->module = $data['name'];
                    $addon->name = $data['alias'];
                    $addon->monthly_price = $data['monthly_price'] ?? 0;
                    $addon->yearly_price = $data['monthly_price'] ?? 0;
                    $addon->package_name = $data['package_name'];
                    $addon->save();
                    Module::moduleCacheForget($request->name);
                }
                $module = Module::find($request->name);
                $check_parent_module = $this->Check_Parent_Module($module);
                if ($check_parent_module['status'] == true) {
                    Artisan::call('migrate --path=/packages/workdo/' . $request->name . '/src/database/migrations');
                    Artisan::call('package:seed ' . $request->name);
                    $module->enable();
                    Module::moduleCacheForget($request->name);
                    return redirect()->back()->with('success', __('Module Enable Successfully!'));
                } else {
                    return redirect()->back()->with('error', __($check_parent_module['msg']));
                }
            }
        }
    }

    public function remove($moduleName)
    {
        if (auth()->user() && auth()->user()->isAbleTo('Module remove')) {
            $module = Module::find($moduleName);
            if($module) {
                $module->disable();
                Permission::where('module',$moduleName)->delete();
                AddOnManager::where('name',$moduleName)->delete();
                // Remove the temporary directory
                $this->deleteDirectory('packages/workdo/' . $moduleName);
                // Sidebar Performance Changes
                sideMenuCacheForget('all');
                return redirect()->back()->with('success', __('Module delete successfully!'));
            } else {
                return redirect()->back()->with('error', __('Oops something went wrong!'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function Check_Child_Module($module)
    {
        $path =$module->getPath().'/module.json';

        $module_json = json_decode(file_get_contents($path));
        $json=(array)$module_json;
        $status = true;
        if(isset($json['child_module']) && !empty($json['child_module']))
        {
            foreach ($json['child_module'] as $key => $value)
            {
                $child_module = module_is_active($value);
                if($child_module == true)
                {
                    $module = Module::find($value);
                    $module->disable();
                    if($module)
                    {
                        $this->Check_Child_Module($module);
                    }
                }
            }
            return true;
        }
        else
        {
            return true;
        }
    }

    public function Check_Parent_Module($module)
    {
        $path =$module->getPath().'/module.json';
        $json = json_decode(file_get_contents($path), true);
        $data['status'] = true;
        $data['msg'] = '';

        if(isset($json['parent_module']) && !empty($json['parent_module']))
        {
            foreach ($json['parent_module'] as $key => $value) {
                $modules = implode(',',$json['parent_module']);
                $parent_module = module_is_active($value);
                if($parent_module == true)
                {
                    $module = Module::find($value);
                    if($module)
                    {
                         $this->Check_Parent_Module($module);
                    }
                }
                else
                {
                    $data['status'] = false;
                    $data['msg'] = 'please activate this module '.$modules;
                    return $data;
                }
            }
            return $data;
        }
        else
        {
            return $data;
        }
    }

    public function install(Request $request)
    {
        $zip = new ZipArchive;
        $fileName = $request->file('file')->getClientOriginalName();
        $fileName = str_replace('.zip', '', $fileName);

        try {
            $res = $zip->open($request->file);
        } catch (\Exception $e) {
            $return['status'] = 'error';
            $return['message'] = $e->getMessage();
            return response()->json($return);
        }
        if ($res === TRUE) {
            // Define the target directory
            $extractPath = 'packages/workdo/' . $fileName;

            // Ensure the directory exists (create it if it doesn't)
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
                // Check if chmod exists
                if (function_exists('chmod')) {
                    @chmod($extractPath, 0755); // Set permissions if possible
                    @chmod('packages/workdo', 0755);
                }
            } else {
                // Check if chmod exists
                if (function_exists('chmod')) {
                    @chmod($extractPath, 0755); // Set permissions if possible
                    @chmod('packages/workdo', 0755); // Set permissions if possible
                }
            }

            // After extracting to the temporary directory
            $tempPath = 'packages/workdo/tmp_' . uniqid();
            if (function_exists('chmod')) {
                @chmod($tempPath, 0755); // Set permissions if possible
            }
            $zip->extractTo($tempPath);
            $zip->close();

            // Determine the root folder name in the zip (if needed)
            $rootFolder = array_diff(scandir($tempPath), ['.', '..']);
            if (empty($rootFolder)) {
                $return['status'] = 'error';
                $return['message'] = __('The zip file is empty or does not contain any valid files.');
                return response()->json($return);
            }

            $rootFolderName = array_values($rootFolder)[0]; // Get the first folder name in the zip

            // Move files to the target directory
            $this->moveExtractedFiles($tempPath, $extractPath, $rootFolderName);

            // Remove the temporary directory
            $this->deleteDirectory($tempPath);
            if (function_exists('chmod')) {
                @chmod($extractPath, 0755); // Set permissions if possible
            }

            $filePath = base_path('packages/workdo/' . $fileName . '/module.json');
            if (file_exists($filePath)) {
                $jsonContent = file_get_contents($filePath);
                $data = json_decode($jsonContent, true);
            }

            $addon = AddOnManager::where('module', $fileName)->first();
            if (empty($addon)) {
                $addon = new AddOnManager;
                $addon->module = $data['name'] ?? $fileName;
                $addon->name = $data['alias'] ?? $fileName;
                $addon->monthly_price = 0;
                $addon->yearly_price = 0;
                $addon->is_enable = 0;
                $addon->is_display = $data['display'] ?? 1;
                $addon->package_name = $data['package_name'] ?? $fileName;
                $addon->save();
            }
            Module::moduleCacheForget($addon->module);
            $return['status'] = 'success';
            $return['message'] = __('Install successfully');
            return response()->json($return);
        } else {
            $return['status'] = 'error';
            $return['message'] = __('Something went wrong');
            return response()->json($return);
        }
        $return['status'] = 'error';
        $return['message'] = __('Something went wrong');
        return response()->json($return);
    }

    public function add(){
        if(auth()->user() && auth()->user()->isAbleTo('Module Add'))
        {
            return view('module.add');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Move files from one directory to another.
     *
     * @param string $source
     * @param string $destination
     */
    private function moveExtractedFiles($source, $destination, $filename = null)
    {
        // Adjust the source directory if a root folder (e.g., $filename) exists in the zip
        if ($filename) {
            $source = $source . DIRECTORY_SEPARATOR . $filename;
        }

        $files = array_diff(scandir($source), ['.', '..']);
        foreach ($files as $file) {
            $srcPath = $source . DIRECTORY_SEPARATOR . $file;
            $destPath = $destination . DIRECTORY_SEPARATOR . $file;

            if (is_dir($srcPath)) {
                // Recursively move subdirectories
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
                // Check if chmod exists
                if (function_exists('chmod')) {
                    @chmod($destPath, 0755); // Set permissions if possible
                }
                $this->moveExtractedFiles($srcPath, $destPath);
            } else {
                // Move file
                rename($srcPath, $destPath);
                // Check if chmod exists
                if (function_exists('chmod')) {
                    @chmod($destPath, 0644); // Set permissions if possible
                }
            }
        }
    }

    /**
     * Delete a directory and its contents.
     *
     * @param string $dirPath
     * @return bool
     */
    private function deleteDirectory($dirPath)
    {
        if (!is_dir($dirPath)) {
            return false;
        }

        $items = array_diff(scandir($dirPath), ['.', '..']);
        foreach ($items as $item) {
            $path = $dirPath . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dirPath);
    }
}
